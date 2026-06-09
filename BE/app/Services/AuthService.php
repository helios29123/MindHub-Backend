<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\AuthSession;
use App\Models\User;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    private const PASSWORD_RESET_EXPIRES_MINUTES = 15;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SessionRepository $sessionRepository,
        private readonly AccessTokenService $accessTokenService,
        private readonly GoogleTokenVerifier $googleTokenVerifier
    ) {
    }

    // AUTH01: Đăng ký
    public function register(array $registerData): User
    {
        if ($this->userRepository->existsByEmail($registerData['email'])) {
            throw new BusinessException('Email đã được sử dụng.', 409, [
                'email' => ['Email đã được sử dụng.'],
            ]);
        }

        return DB::transaction(function () use ($registerData) {
            return $this->userRepository->create([
                'full_name' => $registerData['full_name'],
                'email' => $registerData['email'],
                'phone' => $registerData['phone'] ?? null,
                'password_hash' => Hash::make($registerData['password']),
                'oauth_account_login' => null,
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => null,
            ]);
        });
    }

    // AUTH03: Đăng nhập
    public function login(array $loginData, Request $request): array
    {
        $user = $this->userRepository->findByEmail($loginData['email']);

        if (! $user || ! $user->password_hash || ! Hash::check($loginData['password'], (string) $user->password_hash)) {
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

    public function googleLogin(array $googleLoginData, Request $request): array
    {
        $googleUser = $this->googleTokenVerifier->verify($googleLoginData['google_token']);

        return DB::transaction(function () use ($googleUser, $googleLoginData, $request) {
            $user = $this->userRepository->findByOAuthProviderId(
                $googleUser['provider'],
                $googleUser['provider_id']
            );

            if (! $user) {
                $user = $this->userRepository->findByEmail($googleUser['email']);
            }

            if ($user) {
                $this->ensureUserCanLogin($user);

                $user = $this->userRepository->update($user, [
                    'oauth_account_login' => json_encode([
                        'provider' => $googleUser['provider'],
                        'provider_id' => $googleUser['provider_id'],
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
                        'provider' => $googleUser['provider'],
                        'provider_id' => $googleUser['provider_id'],
                    ], JSON_THROW_ON_ERROR),
                    'role' => User::ROLE_LEARNER,
                    'status' => User::STATUS_ACTIVE,
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
            ? now()->parse($passwordResetData['expires_at'])
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

    public function logout(AuthSession $session): void
    {
        $this->sessionRepository->revoke($session);
    }

    public function verifyEmailBlocked(): void
    {
        throw new BusinessException('Chức năng chưa sẵn sàng triển khai trong Sprint 1.', 501);
    }

    private function ensureUserCanLogin(User $user): void
    {
        if (! $user->isActive()) {
            throw new BusinessException('Tài khoản không được phép đăng nhập.', 403);
        }
    }

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
            'user' => $user->refresh(),
            'access_token' => $accessToken['token'],
            'refresh_token' => $refreshToken['token'],
            'expires_in' => $accessToken['expires_in'],
            'session' => $session,
        ];
    }
}
