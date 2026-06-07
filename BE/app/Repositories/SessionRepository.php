<?php
namespace App\Repositories;
use App\Models\AuthSession;


class SessionRepository
{
    public function create(array $sessionData)
    {
        return AuthSession::create($sessionData);
    }

    public function findActiveById(int $sessionId)
    {
        return AuthSession::query()
            ->where('id', $sessionId)
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    public function update(AuthSession $session, array $sessionData)
    {
        $session->fill($sessionData);
        $session->save();

        return $session->refresh();
    }

    public function revoke(AuthSession $session)
    {
        return $this->update($session, [
            'revoked_at' => now(),
        ]);
    }

    public function revokeAllByUserId(int $userId)
    {
        return AuthSession::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }

    public function findByRefreshTokenHash(string $refreshTokenHash)
    {
        return AuthSession::query()
            ->where('refresh_token_hash', $refreshTokenHash)
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }
}
