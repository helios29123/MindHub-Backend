<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use App\Mail\VerifyEmailMail;
use App\Models\AuthSession;
use App\Models\User;
use App\Repositories\Instructor\InstructorProfileRepository;
use App\Repositories\Instructor\PayoutAccountRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserSessionRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        private readonly PayoutAccountRepository $payoutAccountRepository
    ) {
    }

    /**
     * Route cũ /api/auth/register
     * Mặc định xử lý như đăng ký learner để không làm hỏng frontend cũ.
     */
    public function register(array $registerData): array
    {
        return $this->registerLearner($registerData);
    }

    /**
     * AUTH-01: Đăng ký học viên.
     *
     * Luồng:
     * - Check email trùng.
     * - Check phone trùng.
     * - Tạo user role=learner, status=inactive.
     * - Gửi email xác thực.
     * - Sau khi verify email, learner mới được active.
     */
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
                'oauth_account_login' => null,
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

    /**
     * AUTH-01 mở rộng: Đăng ký giảng viên.
     *
     * Luồng:
     * - Check email trùng.
     * - Check phone trùng.
     * - Tạo user role=instructor, status=inactive.
     * - Tạo instructor_profiles.
     * - Tạo payout_accounts status=pending_verification.
     * - Gửi email xác thực.
     * - Sau khi verify email, instructor vẫn inactive để chờ admin duyệt.
     */
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
                'oauth_account_login' => null,
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

            // $this->payoutAccountRepository->create([
            //     'user_id' => $user->id,
            //     'provider' => $registerData['bank_provider'] ?? 'Chưa cập nhật',
            //     'account_number' => $registerData['bank_account_number'] ?? 'Chưa cập nhật',
            //     'account_name' => $registerData['bank_account_name'] ?? 'Chưa cập nhật',
            //     'connected_at' => null,
            //     'status' => 'pending_verification',
            // ]);

            $verifyUrl = $this->sendVerifyEmail($user);

            return [
                'user' => $user->refresh(),
                'verify_url' => config('app.debug') ? $verifyUrl : null,
                'note' => 'Tài khoản giảng viên cần xác thực email và chờ admin duyệt hồ sơ.',
            ];
        });
    }

    /**
     * AUTH-02: Tạo link xác thực email có chữ ký và thời hạn.
     */
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

    /**
     * AUTH-02: Gửi email xác thực.
     *
     * Nếu MAIL_MAILER=log thì email sẽ ghi vào storage/logs/laravel.log.
     * Nếu MAIL_MAILER=smtp thì gửi mail thật.
     */
    public function sendVerifyEmail(User $user): string
    {
        $verifyUrl = $this->createEmailVerificationUrl($user);

        Mail::to($user->email)->send(
            new VerifyEmailMail($user, $verifyUrl)
        );

        return $verifyUrl;
    }

    /**
     * AUTH-02: Xác thực email.
     *
     * Learner:
     * - email_verified_at = now()
     * - status = active
     *
     * Instructor:
     * - email_verified_at = now()
     * - status vẫn inactive để chờ admin duyệt hồ sơ
     */
    public function verifyEmail(int $userId, string $hash): User
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new BusinessException('Không tìm thấy người dùng.', 404);
        }

        if (! hash_equals(sha1($user->email), $hash)) {
            throw new BusinessException('Link xác thực email không hợp lệ.', 403);
        }

        if ($user->hasVerifiedEmail()) {
            return $user->refresh();
        }

        $updateData = [
            'email_verified_at' => now(),
        ];

        if ($user->role === User::ROLE_LEARNER) {
            $updateData['status'] = User::STATUS_ACTIVE;
        }

        return $this->userRepository->update($user, $updateData);
    }

    /**
     * AUTH-02: Gửi lại link xác thực email.
     */
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

    /**
     * AUTH-03: Đăng nhập bằng email/password.
     */
    public function login(array $loginData, Request $request): array
    {
        $user = $this->userRepository->findByEmail($loginData['email']);

        if (
            ! $user ||
            ! $user->password_hash ||
            ! Hash::check($loginData['password'], (string) $user->password_hash)
        ) {
            throw new BusinessException('Email hoặc mật khẩu không đúng.', 401);
        }

        $this->ensureUserCanLogin($user);

        return DB::transaction(function () use ($user, $loginData, $request) {
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

    /**
     * AUTH-04: Đăng nhập Google.
     *
     * Nếu user chưa tồn tại:
     * - Tạo learner active.
     * - email_verified_at = now().
     *
     * Nếu user đã tồn tại:
     * - Check locked/inactive.
     * - Gắn oauth_account_login.
     * - Cập nhật email_verified_at nếu còn null.
     */
    public function googleLogin(array $googleLoginData, Request $request): array
    {
        $googleUser = $this->googleTokenVerifier->verify($googleLoginData['google_token']);

        return DB::transaction(function () use ($googleUser, $googleLoginData, $request) {
            $provider = $googleUser['provider'] ?? 'google';
            $providerId = $googleUser['provider_id'];

            $user = $this->userRepository->findByOAuthProviderId(
                $provider,
                $providerId
            );

            if (! $user) {
                $user = $this->userRepository->findByEmail($googleUser['email']);
            }

            if ($user) {
                $this->ensureUserCanLoginForGoogle($user);

                $user = $this->userRepository->update($user, [
                    'oauth_account_login' => json_encode([
                        'provider' => $provider,
                        'provider_id' => $providerId,
                    ], JSON_THROW_ON_ERROR),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'last_login_at' => now(),
                ]);
            } else {
                $user = $this->userRepository->create([
                    'full_name' => $googleUser['full_name'],
                    'email' => $googleUser['email'],
                    'password_hash' => null,
                    'phone' => null,
                    'oauth_account_login' => json_encode([
                        'provider' => $provider,
                        'provider_id' => $providerId,
                    ], JSON_THROW_ON_ERROR),
                    'role' => User::ROLE_LEARNER,
                    'status' => User::STATUS_ACTIVE,
                    'locked' => false,
                    'locked_reason' => null,
                    'email_verified_at' => now(),
                    'last_login_at' => now(),
                ]);
            }

            return $this->createAuthenticatedSession(
                $user,
                $googleLoginData['device_name'] ?? null,
                $request
            );
        });
    }

    /**
     * AUTH-05: Quên mật khẩu.
     */
    public function forgotPassword(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (! $user) {
            return [
                'reset_token' => null,
                'expires_at' => null,
            ];
        }

        $plainResetToken = Str::random(64);
        $expiresAt = now()->addMinutes(self::PASSWORD_RESET_EXPIRES_MINUTES);

        $this->userRepository->update($user, [
            'password_reset' => json_encode([
                'token_hash' => hash('sha256', $plainResetToken),
                'expires_at' => $expiresAt->toISOString(),
            ], JSON_THROW_ON_ERROR),
        ]);

        return [
            'reset_token' => config('app.debug') ? $plainResetToken : null,
            'expires_at' => config('app.debug') ? $expiresAt->toISOString() : null,
        ];
    }

    /**
     * AUTH-06: Đặt lại mật khẩu.
     */
    public function resetPassword(array $resetPasswordData): void
    {
        $user = $this->userRepository->findByEmail($resetPasswordData['email']);

        if (! $user || ! $user->password_reset) {
            throw new BusinessException('Token đặt lại mật khẩu không hợp lệ.', 400, [
                'token' => ['Token đặt lại mật khẩu không hợp lệ.'],
            ]);
        }

        $passwordResetData = json_decode($user->password_reset, true);

        if (! is_array($passwordResetData)) {
            throw new BusinessException('Token đặt lại mật khẩu không hợp lệ.', 400, [
                'token' => ['Token đặt lại mật khẩu không hợp lệ.'],
            ]);
        }

        $tokenHash = $passwordResetData['token_hash'] ?? null;
        $expiresAt = isset($passwordResetData['expires_at'])
            ? Carbon::parse($passwordResetData['expires_at'])
            : null;

        if (
            ! is_string($tokenHash) ||
            ! hash_equals($tokenHash, hash('sha256', $resetPasswordData['token'])) ||
            ! $expiresAt ||
            now()->greaterThan($expiresAt)
        ) {
            throw new BusinessException('Token đặt lại mật khẩu không hợp lệ.', 400, [
                'token' => ['Token đặt lại mật khẩu không hợp lệ.'],
            ]);
        }

        DB::transaction(function () use ($user, $resetPasswordData) {
            $this->userRepository->update($user, [
                'password_hash' => Hash::make($resetPasswordData['password']),
                'password_reset' => null,
            ]);

            $this->userSessionRepository->revokeAllByUserId((int) $user->id);
        });
    }

    /**
     * AUTH-07: Logout.
     */
    public function logout(AuthSession $session): void
    {
        $this->userSessionRepository->revoke($session);
    }

    /**
     * Check email và số điện thoại không trùng.
     */
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

    /**
     * Login thường bắt buộc:
     * - active
     * - không locked
     * - đã xác thực email
     */
    private function ensureUserCanLogin(User $user): void
    {
        if (! $user->isActive() || $user->isLocked()) {
            throw new BusinessException('Tài khoản không được phép đăng nhập.', 403);
        }

        if (! $user->hasVerifiedEmail()) {
            throw new BusinessException('Vui lòng xác thực email trước khi đăng nhập.', 403);
        }
    }

    /**
     * Google login:
     * - locked thì chặn
     * - instructor inactive thì chặn vì đang chờ admin duyệt
     * - learner inactive thì có thể active vì Google đã xác thực email
     */
    private function ensureUserCanLoginForGoogle(User $user): void
    {
        if ($user->isLocked()) {
            throw new BusinessException('Tài khoản không được phép đăng nhập.', 403);
        }

        if ($user->role === User::ROLE_INSTRUCTOR && ! $user->isActive()) {
            throw new BusinessException('Tài khoản giảng viên đang chờ admin duyệt.', 403);
        }

        if ($user->role !== User::ROLE_INSTRUCTOR && ! $user->isActive()) {
            $this->userRepository->update($user, [
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        }
    }

    /**
     * Tạo session đăng nhập:
     * - refresh_token lưu hash trong bảng sessions
     * - access_token trả về cho client
     */
    private function createAuthenticatedSession(User $user, ?string $deviceName, Request $request): array
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
