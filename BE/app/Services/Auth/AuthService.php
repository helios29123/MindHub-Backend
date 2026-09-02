<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use App\Mail\VerifyEmailMail;
use App\Models\Notification;
use App\Models\PayoutAccount;
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

use App\Services\Notification\SpeedSmsService;

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
        private readonly OtpService $otpService,
        private readonly SpeedSmsService $speedSmsService
    ) {}

    public function register(array $registerData): array
    {
        return $this->registerLearner($registerData);
    }

    public function registerLearner(array $registerData): array
    {
        $hasEmail = ! empty($registerData['email']);
        $hasPhone = ! empty($registerData['phone']);

        $emailInput = $hasEmail ? strtolower(trim($registerData['email'])) : null;
        $phoneInput = $hasPhone ? trim($registerData['phone']) : null;

        $this->ensureEmailAndPhoneAreUnique(
            $emailInput,
            $phoneInput
        );

        $dbEmail = $emailInput ?: ($phoneInput . '@phone.mindhub.vn');

        return DB::transaction(function () use ($registerData, $dbEmail, $phoneInput, $hasEmail, $hasPhone) {
            $user = $this->userRepository->create([
                'full_name' => $registerData['full_name'],
                'email' => $dbEmail,
                'phone' => $phoneInput,
                'password_hash' => Hash::make($registerData['password']),
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_INACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'email_verified_at' => null,
            ]);

            $otpCode = $this->otpService->generate((int) $user->id, 'email_verification', self::VERIFY_EMAIL_EXPIRES_MINUTES * 60);
            $verifyUrl = null;
            $channel = ($hasPhone && ! $hasEmail) ? 'sms' : 'email';

            if ($channel === 'sms') {
                try {
                    $this->speedSmsService->sendOtp((string) $phoneInput, (string) $otpCode);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('SMS send failed: ' . $e->getMessage());
                }
            } else {
                $verifyUrl = $this->sendVerifyEmail($user, $otpCode);
            }

            return [
                'user' => $user->refresh(),
                'verify_url' => config('app.debug') ? $verifyUrl : null,
                'otp_code' => $otpCode,
                'channel' => $channel,
                'sent_to' => $channel === 'sms' ? $phoneInput : $dbEmail,
            ];
        });
    }

    public function registerInstructor(array $registerData): array
    {
        $email = strtolower(trim((string) $registerData['email']));
        $existingUser = $this->userRepository->findByEmail($email);

        $isRejectedResubmit = false;
        if ($existingUser) {
            $latestPayout = PayoutAccount::where('user_id', $existingUser->id)->orderByDesc('id')->first();
            if ($existingUser->role === User::ROLE_INSTRUCTOR && $latestPayout?->status === 'disabled') {
                $isRejectedResubmit = true;
            } else {
                throw new BusinessException('Email đã được sử dụng.', 409, [
                    'email' => ['Email đã được sử dụng. Vui lòng sử dụng email mới để đăng ký Giảng viên.'],
                ]);
            }
        }

        if (! $isRejectedResubmit) {
            $this->ensureEmailAndPhoneAreUnique(
                $email,
                $registerData['phone'] ?? null
            );
        }

        return DB::transaction(function () use ($registerData, $email, $existingUser, $isRejectedResubmit) {
            if ($isRejectedResubmit && $existingUser) {
                $user = $existingUser;
                $user->full_name = $registerData['full_name'];
                if (! empty($registerData['phone'])) {
                    $user->phone = $registerData['phone'];
                }
                if (! empty($registerData['password'])) {
                    $user->password_hash = Hash::make($registerData['password']);
                }
                $user->status = User::STATUS_INACTIVE;
                $user->save();

                $profile = $this->instructorProfileRepository->findProfileByUserId((int) $user->id);
                if ($profile) {
                    $profile->update([
                        'bio' => $registerData['bio'] ?? $profile->bio,
                        'expertise' => $registerData['expertise'] ?? $profile->expertise,
                        'experience_years' => $registerData['experience_years'] ?? $profile->experience_years,
                        'level' => $registerData['level'] ?? $profile->level,
                        'updated_at' => now(),
                    ]);
                } else {
                    $this->instructorProfileRepository->create([
                        'user_id' => $user->id,
                        'bio' => $registerData['bio'] ?? null,
                        'expertise' => $registerData['expertise'] ?? null,
                        'experience_years' => $registerData['experience_years'] ?? 0,
                        'level' => $registerData['level'] ?? null,
                    ]);
                }

                $payout = PayoutAccount::where('user_id', $user->id)->orderByDesc('id')->first();
                if ($payout) {
                    $payout->update([
                        'provider' => $registerData['bank_provider'] ?? $payout->provider,
                        'account_number' => $registerData['bank_account_number'] ?? $payout->account_number,
                        'account_name' => $registerData['bank_account_name'] ?? $user->full_name,
                        'status' => 'pending_verification',
                        'is_default' => false,
                        'disabled_at' => null,
                        'verified_at' => null,
                        'updated_at' => now(),
                    ]);
                } else {
                    $this->payoutAccountRepository->create([
                        'user_id' => $user->id,
                        'provider' => $registerData['bank_provider'] ?? 'Chưa liên kết',
                        'account_number' => $registerData['bank_account_number'] ?? 'CHUA_CO',
                        'account_name' => $registerData['bank_account_name'] ?? $user->full_name,
                        'status' => 'pending_verification',
                        'is_default' => false,
                    ]);
                }

                try {
                    $admins = User::where('role', 'admin')->get();
                    foreach ($admins as $admin) {
                        Notification::create([
                            'user_id' => $admin->id,
                            'type' => 'instructor_upgrade_request',
                            'title' => 'Giảng viên nộp lại hồ sơ',
                            'message' => "Giảng viên {$user->full_name} ({$user->email}) đã cập nhật và nộp lại hồ sơ xét duyệt.",
                            'action_url' => '/admin/instructor-upgrades',
                            'channel' => 'web',
                        ]);
                    }
                } catch (\Throwable $e) {}
            } else {
                $user = $this->userRepository->create([
                    'full_name' => $registerData['full_name'],
                    'email' => $email,
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

                $this->payoutAccountRepository->create([
                    'user_id' => $user->id,
                    'provider' => $registerData['bank_provider'] ?? 'Chưa liên kết',
                    'account_number' => $registerData['bank_account_number'] ?? 'CHUA_CO',
                    'account_name' => $registerData['bank_account_name'] ?? $user->full_name,
                    'status' => 'pending_verification',
                    'is_default' => false,
                ]);
            }

            $otpCode = $this->otpService->generate((int) $user->id, 'email_verification', 3600);
            $verifyUrl = $this->sendVerifyEmail($user, $otpCode);

            return [
                'user' => $user->refresh(),
                'verify_url' => config('app.debug') ? $verifyUrl : null,
                'otp_code' => $otpCode,
                'is_resubmission' => $isRejectedResubmit,
                'note' => $isRejectedResubmit 
                    ? 'Hồ sơ đã được nộp lại thành công. Vui lòng nhập mã OTP để xác thực tài khoản.'
                    : 'Tài khoản giảng viên đã được tạo. Vui lòng nhập mã OTP để xác thực tài khoản và số điện thoại.',
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

        $isInstructor = ($user->role === User::ROLE_INSTRUCTOR);

        $updatedUser = $this->userRepository->update($user, [
            'email_verified_at' => now(),
            'status' => $isInstructor ? User::STATUS_INACTIVE : User::STATUS_ACTIVE,
        ]);

        if ($isInstructor) {
            try {
                $adminUsers = User::where('role', 'admin')->get();
                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'instructor_upgrade_request',
                        'title' => 'Yêu cầu đăng ký Giảng viên mới',
                        'message' => "Học viên {$user->full_name} ({$user->email}) đã xác thực OTP và gửi hồ sơ đăng ký làm Giảng viên.",
                        'action_url' => '/admin/instructor-upgrades',
                        'channel' => 'web',
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send admin notification for instructor registration: ' . $e->getMessage());
            }

            try {
                $adminEmail = config('mail.admin_address', 'dominhdang3010@gmail.com');
                Mail::to($adminEmail)->send(
                    new \App\Mail\InstructorUpgradeRequestedMail($user, [
                        'bio' => $user->instructorProfile?->bio,
                        'expertise' => $user->instructorProfile?->expertise,
                        'experience_years' => $user->instructorProfile?->experience_years,
                        'bank_provider' => 'Chưa liên kết',
                        'bank_account_number' => 'N/A',
                        'bank_account_name' => $user->full_name,
                    ])
                );
            } catch (\Throwable $e) {}
        }

        return $updatedUser;
    }

    public function resendVerifyOtp(string $identifier, string $channel = 'email', ?string $fallbackEmail = null, ?string $fallbackPhone = null): array
    {
        $identifier = trim($identifier);
        $user = null;

        if (! empty($identifier)) {
            $user = $this->userRepository->findByEmail(strtolower($identifier))
                ?: User::query()->where('phone', $identifier)->first();
        }

        if (! $user && ! empty($fallbackEmail)) {
            $user = $this->userRepository->findByEmail(strtolower(trim($fallbackEmail)));
        }

        if (! $user && ! empty($fallbackPhone)) {
            $user = User::query()->where('phone', trim($fallbackPhone))->first();
        }

        if (! $user) {
            throw new BusinessException('Không tìm thấy tài khoản tương ứng với thông tin đăng ký.', 404);
        }

        if ($user->hasVerifiedEmail()) {
            throw new BusinessException('Tài khoản đã được xác thực trước đó.', 400, [
                'email' => ['Tài khoản đã được xác thực trước đó.'],
            ]);
        }

        // Cập nhật số điện thoại nếu người dùng nhập mới SĐT xác thực
        if (! empty($fallbackPhone) && empty($user->phone)) {
            $user->phone = trim($fallbackPhone);
            $user->save();
        }

        $otpCode = $this->otpService->generate(
            (int) $user->id,
            'email_verification',
            self::VERIFY_EMAIL_EXPIRES_MINUTES * 60
        );

        $verifyUrl = null;
        if ($channel === 'sms' || $channel === 'phone') {
            $phoneTarget = $user->phone ?: $fallbackPhone ?: $identifier;
            \Illuminate\Support\Facades\Log::info("SMS OTP sent to {$phoneTarget}: {$otpCode}");
            // Kích hoạt gửi tin nhắn SMS thật qua SpeedSMS Gateway
            $this->speedSmsService->sendOtp((string) $phoneTarget, (string) $otpCode);
        } else {
            // Gửi Email OTP
            $verifyUrl = $this->sendVerifyEmail($user, $otpCode);
        }

        return [
            'verify_url' => $verifyUrl,
            'otp_code' => $otpCode,
            'channel' => $channel,
            'sent_to' => ($channel === 'sms' || $channel === 'phone') ? ($user->phone ?? $fallbackPhone ?? $identifier) : ($user->email ?? $fallbackEmail ?? $identifier),
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
        $loginId = trim($loginData['email']);
        $user = $this->userRepository->findByEmail(strtolower($loginId));
        if (! $user) {
            $user = User::query()->where('phone', $loginId)->first();
        }

        if (
            ! $user ||
            ! $user->password_hash ||
            ! Hash::check($loginData['password'], (string) $user->password_hash)
        ) {
            throw new BusinessException('Email/Số điện thoại hoặc mật khẩu không đúng.', 401);
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

    private function ensureEmailAndPhoneAreUnique(?string $email, ?string $phone): void
    {
        if ($email !== null && $this->userRepository->existsByEmail($email)) {
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
                : ($user->role === User::ROLE_INSTRUCTOR
                    ? 'Tài khoản giảng viên của bạn đã xác thực OTP và đang chờ Quản trị viên (Admin) phê duyệt hồ sơ.'
                    : 'Tài khoản đang ngừng hoạt động.');

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
