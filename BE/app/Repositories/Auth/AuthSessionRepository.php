<?php
namespace App\Repositories\Auth;
use App\Models\Session;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
final class AuthSessionRepository
{
    public function paginateByUserId(
        int $userId,
        string $status,
        int $perPage,
        int $page
    ): LengthAwarePaginator {
        $query = Session::query()
            ->select([
                'id',
                'user_id',
                'device_name',
                'ip_address',
                'user_agent',
                'expires_at',
                'revoked_at',
                'created_at',
            ])
            ->where('user_id', $userId);
        $this->applyStatusFilter($query, $status);
        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(
                perPage: $perPage,
                columns: ['*'],
                pageName: 'page',
                page: $page
            );
    }
    private function applyStatusFilter(Builder $query, string $status): void
    {
        $now = now();
        if ($status === 'active') {
            $query
                ->whereNull('revoked_at')
                ->where('expires_at', '>', $now);
            return;
        }
        if ($status === 'expired') {
            $query
                ->whereNull('revoked_at')
                ->where('expires_at', '<=', $now);
            return;
        }
        if ($status === 'revoked') {
            $query->whereNotNull('revoked_at');
        }
    }
}