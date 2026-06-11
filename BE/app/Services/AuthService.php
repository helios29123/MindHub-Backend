<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Mail\VerifyEmailMail;
use App\Models\AuthSession;
use App\Models\User;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        private readonly SessionRepository $sessionRepository,
        private readonly AccessTokenService $accessTokenService,
        private readonly GoogleTokenVerifier $googleTokenVerifier
    ) {
    }

    /**
     * AUTH01: Đăng ký tài khoản.
     * Sau khi đăng ký sẽ tạo link xác thực email.
     */
    public function register(array $registerData): array
    {
        if ($this->userRepository->existsByEmail($registerData['email'])) {
            throw new BusinessException('Email đã được sử dụng.', 409, [
                'email' => ['Email đã được sử dụng.'],
            ]);
        }

        return DB::transaction(function () use ($registerData) {
            $user = $this->userRepository->create([
                'full_name' => $registerData['full_name'],
                'email' => $registerData['email'],
                'phone' => $registerData['phone'] ?? null,
                'password_hash' => Hash::make($registerData['password']),
                'oauth_account_login' => null,
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
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
     * AUTH02: Tạo link xác thực email.
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
     * AUTH02: Gửi mail xác thực email.
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
     * AUTH02: Xác thực email khi user bấm link.
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

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $user->refresh();
    }

    /**
     * AUTH02: Gửi lại link xác thực email.
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
     * AUTH03: Đăng nhập bằng email/password.
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
     * AUTH04: Đăng nhập Google.
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
                $this->ensureUserCanLogin($user);

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
     * AUTH05: Quên mật khẩu.
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
     * AUTH06: Đặt lại mật khẩu.
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

            $this->sessionRepository->revokeAllByUserId((int) $user->id);
        });
    }

    /**
     * AUTH07: Đăng xuất.
     */
    public function logout(AuthSession $session): void
    {
        $this->sessionRepository->revoke($session);
    }

    /**
     * Kiểm tra user có được phép đăng nhập không.
     */
    private function ensureUserCanLogin(User $user): void
    {
        if (! $user->isActive() || $user->isLocked()) {
            throw new BusinessException('Tài khoản không được phép đăng nhập.', 403);
        }
    }

    /**
     * Tạo access_token, refresh_token và session.
     */
    private function createAuthenticatedSession(User $user, ?string $deviceName, Request $request): array
    {
        $refreshToken = $this->accessTokenService->createRefreshToken();

        $session = $this->sessionRepository->create([
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