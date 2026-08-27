<?php
namespace App\Repositories\Auth;
use App\Models\Session;


class SessionRepository
{
    public function create(array $sessionData)
    {
        return Session::create($sessionData);
    }

    public function findActiveById(int $sessionId)
    {
        return Session::query()
            ->where('id', $sessionId)
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    public function update(Session $session, array $sessionData)
    {
        $session->fill($sessionData);
        $session->save();

        return $session->refresh();
    }

    public function revoke(Session $session)
    {
        return $this->update($session, [
            'revoked_at' => now(),
        ]);
    }

    public function revokeAllByUserId(int $userId)
    {
        return Session::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }

    public function findByRefreshTokenHash(string $refreshTokenHash)
    {
        return Session::query()
            ->where('refresh_token_hash', $refreshTokenHash)
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }
}
