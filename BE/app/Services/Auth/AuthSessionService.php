<?php
namespace App\Services\Auth;
use App\Exceptions\BusinessException;
use App\Repositories\Auth\AuthSessionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
final class AuthSessionService
{
    private const DEFAULT_STATUS = 'active';
    private const DEFAULT_PER_PAGE = 10;
    public function __construct(
        private readonly AuthSessionRepository $authSessionRepository
    ) {
    }
    public function paginateForCurrentUser(int $userId, array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? self::DEFAULT_STATUS;
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : self::DEFAULT_PER_PAGE;
        $page = isset($filters['page']) ? (int) $filters['page'] : 1;
        return $this->authSessionRepository->paginateByUserId(
            userId: $userId,
            status: $status,
            perPage: $perPage,
            page: $page
        );
    }
    public function revokeForCurrentUser(int $userId, int $sessionId): array
    {
        return DB::transaction(function () use ($userId, $sessionId): array {
            $session = $this->authSessionRepository->findByIdAndUserIdForUpdate(
                sessionId: $sessionId,
                userId: $userId
            );
            if (! $session) {
                throw new BusinessException('Không tìm thấy phiên đăng nhập.', 404);
            }
            if ($session->revoked_at !== null) {
                return [
                    'revoked_count' => 0,
                ];
            }
            return [
                'revoked_count' => $this->authSessionRepository->revoke($session),
            ];
        });
    }
    public function logoutAllForCurrentUser(int $userId, int $currentSessionId, bool $keepCurrent): array
    {
        return DB::transaction(function () use ($userId, $currentSessionId, $keepCurrent): array {
            $excludeSessionId = $keepCurrent && $currentSessionId > 0
                ? $currentSessionId
                : null;
            return [
                'revoked_count' => $this->authSessionRepository->revokeActiveByUserId(
                    userId: $userId,
                    excludeSessionId: $excludeSessionId
                ),
            ];
        });
    }
}