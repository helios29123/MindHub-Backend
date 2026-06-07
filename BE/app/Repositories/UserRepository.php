<?php
namespace App\Repositories;

use App\Models\User;
class UserRepository
{
    public function create(array $userData)
    {
        return User::create($userData);
    }

    public function findById(int $userId)
    {
        return User::find($userId);
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function existsByEmail(string $email)
    {
        return User::where('email', $email)->exists();
    }

    public function findByOAuthProviderId(string $provider, string $providerId)
    {
        $providerPattern = '%"provider":"' . addcslashes($provider, '%_\\') . '"%';
        $providerIdPattern = '%"provider_id":"' . addcslashes($providerId, '%_\\') . '"%';

        return User::where('oauth_account_login', 'like', $providerPattern)
            ->where('oauth_account_login', 'like', $providerIdPattern)
            ->first();
    }

    public function update(User $user, array $userData)
    {
        $user->fill($userData);
        $user->save();

        return $user->refresh();
    }
    public function findPublicProfileById(int $id): User
    {
        return User::query()
            ->select([
                'id',
                'full_name',
                'email',
                'phone',
                'role',
                'status',
                'email_verified_at',
            ])
            ->whereKey($id)
            ->firstOrFail();
    }

    public function updateProfileById(int $id, array $data): bool
    {
        $allowedData = array_intersect_key($data, array_flip([
            'full_name',
            'phone',
        ]));

        return User::query()
            ->whereKey($id)
            ->update($allowedData);
    }
}
