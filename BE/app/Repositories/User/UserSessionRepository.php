<?php

namespace App\Repositories\User;

use App\Models\AuthSession;

class UserSessionRepository
{
    public function create(array $data): AuthSession
    {
        return AuthSession::create($data);
    }

    public function findActiveById(int $sessionId): ?AuthSession
    {
        return AuthSession::where('id', $sessionId)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function revoke(AuthSession $session): AuthSession
    {
        $session->forceFill([
            'revoked_at' => now(),
        ])->save();

        return $session->refresh();
    }

    public function revokeAllByUserId(int $userId): int
    {
        return AuthSession::where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }
}
