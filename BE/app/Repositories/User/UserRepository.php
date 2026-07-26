<?php

namespace App\Repositories\User;

use App\Models\User;

class UserRepository
{
    public function existsByEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    public function existsByPhone(string $phone): bool
    {
        return User::where('phone', $phone)->exists();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();
    }

    public function findByOAuthProviderId(string $provider, string $providerId): ?User
    {
        $oauthAccountLogin = json_encode([
            'provider' => $provider,
            'provider_id' => $providerId,
        ], JSON_THROW_ON_ERROR);

        return User::where('oauth_account_login', $oauthAccountLogin)->first();
    }

    public function create(array $userData): User
    {
        return User::create($userData);
    }

    public function update(User $user, array $userData): User
    {
        $user->fill($userData);
        $user->save();

        return $user->refresh();
    }
}
