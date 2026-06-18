<?php
namespace App\Services\Auth;
use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\Auth\AuthSessionRepository;
final class DeviceLimitService
{
    public function __construct(
        private readonly AuthSessionRepository $authSessionRepository
    ) {
    }
    public function assertCanCreateSession(User $user): array
    {
        $meta = $this->sessionLimitMeta($user);
        if (! $this->shouldLimitUser($user)) {
            return $meta;
        }
        if ($meta['active_session_count'] >= $meta['max_sessions']) {
            throw new BusinessException(
                'Tài khoản đã đạt giới hạn thiết bị đăng nhập.',
                409,
                [
                    'sessions' => [
                        'Bạn đã đạt giới hạn thiết bị đăng nhập. Vui lòng đăng xuất thiết bị khác rồi thử lại.',
                    ],
                ]
            );
        }
        return $meta;
    }
    public function sessionLimitMeta(User $user): array
    {
        $activeSessionCount = $this->authSessionRepository->countActiveByUserId((int) $user->id);
        return [
            'active_session_count' => $activeSessionCount,
            'max_sessions' => $this->maxAllowedSessions($user),
        ];
    }
    private function shouldLimitUser(User $user): bool
    {
        return $user->role === User::ROLE_LEARNER;
    }
    private function maxAllowedSessions(User $user): int
    {
        if (! $this->shouldLimitUser($user)) {
            return PHP_INT_MAX;
        }
        $configuredLimit = (int) config('mindhub.max_learner_sessions', 2);
        return max(1, $configuredLimit);
    }
}