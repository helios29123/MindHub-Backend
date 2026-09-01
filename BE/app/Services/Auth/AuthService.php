<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use App\Mail\VerifyEmailMail;
use App\Models\Session;
use App\Models\User;
use App\Repositories\Instructor\InstructorProfileRepository;
use App\Repositories\Instructor\PayoutAccountRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserSessionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AuthService
{
    private const PASSWORD_RESET_EXPIRES_MINUTES = 15;
    private const VERIFY_EMAIL_EXPIRES_MINUTES = 60;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserSessionRepository $userSessionRepository,
        private readonly AccessTokenService $accessTokenService,
        private readonly GoogleTokenVerifier $googleTokenVerifier,
        private readonly InstructorProfileRepository $instructorProfileRepository,
        private readonly PayoutAccountRepository $payoutAccountRepository,
        private readonly OtpService $otpService
    ) {}

    public function register(array $registerData): array
    {
        return $this->registerLearner($registerData);
    }

    public function registerLearner(array $registerData): array
    {
        $this->ensureEmailAndPhoneAreUnique(
            $registerData['email'],
            $registerData['phone'] ?? null
        );

        return DB::transaction(function () use ($registerData) {
            $user = $this->userRepository->create([
                'full_name' => $registerData['full_name'],
                'email' => $registerData['email'],
                'phone' => $registerData['phone'] ?? null,
                'password_hash' => Hash::make($registerData['password']),
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_INACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'email_verified_at' => null,
            ]);

            $verifyUrl = $this->sendVerifyEmail($user);

            return [
                'user' => $user->refresh(),
                'verify_url' => config('app.debug') ? $verifyUrl : null,
            ];
        });
    }

    public function registerInstructor(array $registerData): array
    {
        $this->ensureEmailAndPhoneAreUnique(
            $registerData['email'],
            $registerData['phone'] ?? null
        );

        return DB::transaction(function () use ($registerData) {
            $user = $this->userRepository->create([
                'full_name' => $registerData['full_name'],
                'email' => $registerData['email'],
                'phone' => $registerData['phone'] ?? null,
                'password_hash' => Hash::make($registerData['password']),
                'role' => User::ROLE_INSTRUCTOR,
                'status' => User::STATUS_INACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'email_verified_at' => null,
            ]);

            $this->instructorProfileRepository->create([
                'user_id' => $user->id,
                'bio' => $registerData['bio'] ?? null,
                'expertise' => $registerData['expertise'] ?? null,
                'experience_years' => $registerData['experience_years'] ?? 0,
                'level' => $registerData['level'] ?? null,
            ]);

            $otpCode = $this->otpService->generate((int) $user->id, 'email_verification', 3600);
            $verifyUrl = $this->sendVerifyEmail($user, $otpCode);

            return [
                'user' => $user->refresh(),
                'verify_url' => config('app.debug') ? $verifyUrl : null,
                'otp_code' => $otpCode,
                'note' => 'Tài khoản giảng viên đã được tạo. Vui lòng nhập mã OTP để xác thực tài khoản và số điện thoại.',
            ];
        });
    }

    public function createEmailVerificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'auth.verify-email',
            now()->addMinutes(self::VERIFY_EMAIL_EXPIRES_MINUTES),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );
    }

    public function sendVerifyEmail(User $user, ?string $otpCode = null): string
    {
        $verifyUrl = $this->createEmailVerificationUrl($user);

        if (! $otpCode) {
            $otpCode = $this->otpService->generate(
                (int) $user->id,
                'email_verification',
                self::VERIFY_EMAIL_EXPIRES_MINUTES * 60
            );
        }

        try {
            Mail::to($user->email)->send(
                new VerifyEmailMail($user, $verifyUrl, $otpCode)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(
                'Failed to send verification email to ' . $user->email . ': ' . $e->getMessage()
            );
        }

        return $verifyUrl;
    }

    public function verifyEmail(int $userId, string $hash): User
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new BusinessException('Không tìm thấy người dùng.', 404);
        }

        if (! hash_equals(sha1($user->email), $hash)) {
            throw new BusinessException('Link xác thực email không hợp lệ.', 403);
        }

        $updateData = [
            'email_verified_at' => now(),
            'status' => User::STATUS_ACTIVE,
        ];

        return $this->userRepository->update($user, $updateData);
    }

    public function verifyOtp(string $identifier, string $otp): User
    {
        $identifier = trim($identifier);
        $user = $this->userRepository->findByEmail(strtolower($identifier));
        if (! $user && ! empty($identifier)) {
            $user = User::query()->where('phone', $identifier)->first();
        }

        if (! $user) {
            throw new BusinessException('Không tìm thấy thông tin tài khoản hợp lệ.', 404);
        }

        $this->otpService->verify(
            (int) $user->id,
            'email_verification',
            $otp
        );

        return $this->userRepository->update($user, [
            'email_verified_at' => now(),
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function resendVerifyOtp(string $identifier, string $channel = 'email'): array
    {
        $identifier = trim($identifier);
        $user = $this->userRepository->findByEmail(strtolower($identifier));
        if (! $user && ! empty($identifier)) {
            $user = User::query()->where('phone', $identifier)->first();
        }

        if (! $user) {
            return [
                'verify_url' => null,
                'otp_code' => null,
                'channel' => $channel,
            ];
        }

        if ($user->hasVerifiedEmail()) {
            throw new BusinessException('Tài khoản đã được xác thực trước đó.', 400, [
                'email' => ['Tài khoản đã được xác thực trước đó.'],
            ]);
        }

        $otpCode = $this->otpService->generate(
            (int) $user->id,
            'email_verification',
            self::VERIFY_EMAIL_EXPIRES_MINUTES * 60
        );

        $verifyUrl = null;
        if ($channel === 'sms' || $channel === 'phone') {
            // Cấu hình ghi log tin nhắn SMS OTP gửi đến số điện thoại
            \Illuminate\Support\Facades\Log::info("SMS OTP sent to {$user->phone}: {$otpCode}");
        } else {
            // Gửi Email OTP
            $verifyUrl = $this->sendVerifyEmail($user, $otpCode);
        }

        return [
            'verify_url' => $verifyUrl,
            'otp_code' => $otpCode,
            'channel' => $channel,
            'sent_to' => ($channel === 'sms' || $channel === 'phone') ? ($user->phone ?? $identifier) : ($user->email ?? $identifier),
        ];
    }

    public function resendVerifyEmail(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (! $user) {
            return [
                'verify_url' => null,
            ];
        }

        if ($user->hasVerifiedEmail()) {
            throw new BusinessException('Email đã được xác thực trước đó.', 400, [
                'email' => ['Email đã được xác thực trước đó.'],
            ]);
        }

        $verifyUrl = $this->sendVerifyEmail($user);

        return [
            'verify_url' => config('app.debug') ? $verifyUrl : null,
        ];
    }

    public function login(array $loginData, Request $request): array
    {
        $user = $this->userRepository->findByEmail(strtolower(trim($loginData['email'])));

        if (
            ! $user ||
            ! $user->password_hash ||
            ! Hash::check($loginData['password'], (string) $user->password_hash)
        ) {
            throw new BusinessException('Email hoặc mật khẩu không đúng.', 401);
        }

        $this->ensureUserCanLogin($user);

        return DB::transaction(function () use ($user, $loginData, $request) {
            Auth::guard('web')->login($user);

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $authPayload = $this->createAuthenticatedSession(
                $user,
                $loginData['device_name'] ?? null,
                $request
            );

            $this->userRepository->update($user, [
                'last_login_at' => now(),
            ]);

            return $authPayload;
        });
    }

    public function googleLogin(array $googleLoginData, Request $request): array
    {
        $googleUser = $this->googleTokenVerifier->verify(
            $googleLoginData['google_token']
        );

        $user = $this->handleGoogleUser($googleUser, $request);

        return $this->createAuthenticatedSession(
            $user,
            $googleLoginData['device_name'] ?? null,
            $request
        );
    }

    public function handleGoogleUser(array $googleUser, Request $request): User
    {
        return DB::transaction(function () use ($googleUser, $request) {
            $user = $this->userRepository->findByEmail($googleUser['email']);

            if ($user) {
                $this->ensureUserCanLoginForGoogle($user);

                $updateData = [
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'last_login_at' => now(),
                ];

                if (! empty($googleUser['avatar'])) {
                    $updateData['avatar_url'] = $googleUser['avatar'];
                }

                $user = $this->userRepository->update($user, $updateData);
            } else {
                $user = $this->userRepository->create([
                    'full_name' => $googleUser['full_name'],
                    'email' => $googleUser['email'],
                    'avatar_url' => $googleUser['avatar'] ?? null,
                    'password_hash' => Hash::make(Str::random(64)),
                    'phone' => null,
                    'role' => User::ROLE_LEARNER,
                    'status' => User::STATUS_ACTIVE,
                    'locked' => false,
                    'locked_reason' => null,
                    'email_verified_at' => now(),
                    'last_login_at' => now(),
                ]);
            }

            Auth::guard('web')->login($user);

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            return $user;
        });
    }

    public function forgotPassword(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (! $user) {
            return [
                'reset_token' => null,
                'expires_at' => null,
            ];
        }

        $ttlSeconds = self::PASSWORD_RESET_EXPIRES_MINUTES * 60;
        $plainResetToken = $this->otpService->generate(
            (int) $user->id,
            'password_reset',
            $ttlSeconds
        );
        $expiresAt = now()->addSeconds($ttlSeconds);

        try {
            Mail::to($user->email)->send(
                new \App\Mail\ResetPasswordMail($user, $plainResetToken)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(
                'Failed to send reset password email to ' . $user->email . ': ' . $e->getMessage()
            );
        }

        return [
            'reset_token' => config('app.debug') ? $plainResetToken : null,
            'expires_at' => config('app.debug') ? $expiresAt->toISOString() : null,
        ];
    }

    public function resetPassword(array $resetPasswordData): void
    {
        $user = $this->userRepository->findByEmail($resetPasswordData['email']);

        if (! $user) {
            throw new BusinessException('Token hoặc thông tin đặt lại mật khẩu không hợp lệ.', 400, [
                'token' => ['Token hoặc thông tin đặt lại mật khẩu không hợp lệ.'],
            ]);
        }

        try {
            $this->otpService->verify(
                (int) $user->id,
                'password_reset',
                (string) ($resetPasswordData['token'] ?? '')
            );
        } catch (BusinessException $e) {
            throw new BusinessException('Token đặt lại mật khẩu không hợp lệ.', 400, [
                'token' => ['Token đặt lại mật khẩu không hợp lệ.'],
            ]);
        }

        DB::transaction(function () use ($user, $resetPasswordData): void {
            $this->userRepository->update($user, [
                'password_hash' => Hash::make($resetPasswordData['password']),
            ]);

            $this->userSessionRepository->revokeAllByUserId((int) $user->id);
        });
    }

    public function logout(?Session $session, Request $request): void
    {
        if ($session) {
            $this->userSessionRepository->revoke($session);
        }
        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    private function ensureEmailAndPhoneAreUnique(string $email, ?string $phone): void
    {
        if ($this->userRepository->existsByEmail($email)) {
            throw new BusinessException('Email đã được sử dụng.', 409, [
                'email' => ['Email đã được sử dụng.'],
            ]);
        }

        if ($phone !== null && $this->userRepository->existsByPhone($phone)) {
            throw new BusinessException('Số điện thoại đã được sử dụng.', 409, [
                'phone' => ['Số điện thoại đã được sử dụng.'],
            ]);
        }
    }

    private function ensureUserCanLogin(User $user): void
    {
        if ($user->isLocked()) {
            throw new BusinessException('Tài khoản đang bị khóa bởi quản trị viên.', 403);
        }

        if ($user->status === User::STATUS_SUSPENDED) {
            throw new BusinessException('Tài khoản đang bị đình chỉ.', 403);
        }

        if ($user->status === User::STATUS_INACTIVE) {
            $message = $user->email_verified_at === null
                ? 'Tài khoản chưa xác thực email.'
                : 'Tài khoản đang ngừng hoạt động.';

            throw new BusinessException($message, 403);
        }

        if (! $user->isActive()) {
            throw new BusinessException('Tài khoản không ở trạng thái cho phép đăng nhập.', 403);
        }
    }

    private function ensureUserCanLoginForGoogle(User $user): void
    {
        if ($user->isLocked()) {
            throw new BusinessException('Tài khoản đang bị khóa bởi quản trị viên.', 403);
        }

        if ($user->status === User::STATUS_SUSPENDED) {
            throw new BusinessException('Tài khoản đang bị đình chỉ.', 403);
        }

        if ($user->status === User::STATUS_INACTIVE) {
            $message = $user->role === User::ROLE_INSTRUCTOR
                ? 'Tài khoản giảng viên chưa được phép hoạt động.'
                : 'Tài khoản đang ngừng hoạt động.';

            throw new BusinessException($message, 403);
        }

        if (! $user->isActive()) {
            throw new BusinessException('Tài khoản không ở trạng thái cho phép đăng nhập.', 403);
        }
    }

    public function createAuthenticatedSession(User $user, ?string $deviceName, Request $request): array
    {
        $refreshToken = $this->accessTokenService->createRefreshToken();

        $session = $this->userSessionRepository->create([
            'user_id' => $user->id,
            'refresh_token_hash' => $refreshToken['token_hash'],
            'device_name' => $deviceName ?: 'api_client',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'expires_at' => $refreshToken['expires_at'],
            'revoked_at' => null,
            'created_at' => now(),
        ]);

        $accessToken = $this->accessTokenService->createAccessToken(
            (int) $user->id,
            (int) $session->id
        );

        return [
            'token_type' => 'Bearer',
            'user' => $user->refresh(),
            'access_token' => $accessToken['token'],
            'refresh_token' => $refreshToken['token'],
            'expires_in' => $accessToken['expires_in'],
            'session' => $session,
        ];
    }
}
