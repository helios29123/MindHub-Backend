<?php
namespace App\Repositories\Auth;
use App\Models\Session;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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
    public function findByIdAndUserIdForUpdate(int $sessionId, int $userId): ?Session
    {
        return Session::query()
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();
    }
    public function findByRefreshTokenHash(string $refreshTokenHash, bool $lockForUpdate = false): ?Session
    {
        $query = Session::query()
            ->with('user')
            ->where('refresh_token_hash', $refreshTokenHash);
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }
        return $query->first();
    }
    public function rotateRefreshToken(
        Session $session,
        string $refreshTokenHash,
        Carbon $expiresAt
    ): Session {
        $session->forceFill([
            'refresh_token_hash' => $refreshTokenHash,
            'expires_at' => $expiresAt,
            'revoked_at' => null,
        ])->save();
        return $session->refresh();
    }
    public function countActiveByUserId(int $userId): int
    {
        return Session::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->count();
    }
    public function revoke(Session $session): int
    {
        if ($session->revoked_at !== null) {
            return 0;
        }
        $session->forceFill([
            'revoked_at' => now(),
        ])->save();
        return 1;
    }
    public function revokeActiveByUserId(int $userId, ?int $excludeSessionId = null): int
    {
        $query = Session::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
        if ($excludeSessionId !== null) {
            $query->where('id', '<>', $excludeSessionId);
        }
        return $query->update([
            'revoked_at' => now(),
        ]);
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