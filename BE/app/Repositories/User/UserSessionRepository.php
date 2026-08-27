<?php

namespace App\Repositories\User;

use App\Models\Session;

class UserSessionRepository
{
    public function create(array $data): Session
    {
        return Session::create($data);
    }

    public function findActiveById(int $sessionId): ?Session
    {
        return Session::where('id', $sessionId)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function revoke(Session $session): Session
    {
        $session->forceFill([
            'revoked_at' => now(),
        ])->save();

        return $session->refresh();
    }

    public function revokeAllByUserId(int $userId): int
    {
        return Session::where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }
}
