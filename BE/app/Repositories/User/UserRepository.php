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
