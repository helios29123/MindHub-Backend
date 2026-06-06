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
}