<?php
namespace App\Services\Auth;
use App\Repositories\Auth\AuthSessionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
}