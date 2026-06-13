<?php

namespace App\Repositories\User;

use App\Models\User;

final class UserProfileRepository
{
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

    public function findPasswordCredentialById(int $id): User
    {
        return User::query()
            ->select([
                'id',
                'password_hash',
            ])
            ->whereKey($id)
            ->firstOrFail();
    }

    public function updatePasswordById(int $id, string $passwordHash): bool
    {
        return User::query()
            ->whereKey($id)
            ->update([
                'password_hash' => $passwordHash,
                'password_reset' => null,
            ]);
    }
}