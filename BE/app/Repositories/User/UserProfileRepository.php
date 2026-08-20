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
                'avatar_url',
                'role',
                'status',
                'email_verified_at',
                'last_login_at',
                'created_at',
                'updated_at',
            ])
            ->whereKey($id)
            ->firstOrFail();
    }

    public function updateProfileById(int $id, array $data): bool
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }

        $fullName = $data['full_name'] ?? $data['name'] ?? null;
        $allowedUserData = [];
        if ($fullName !== null) {
            $allowedUserData['full_name'] = $fullName;
        }
        if (array_key_exists('phone', $data)) {
            $allowedUserData['phone'] = $data['phone'];
        }

        if (! empty($allowedUserData)) {
            $user->update($allowedUserData);
        }

        if (array_key_exists('bio', $data) && $data['bio'] !== null) {
            if ($user->role === User::ROLE_INSTRUCTOR) {
                \App\Models\InstructorProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    ['bio' => $data['bio']]
                );
            }
        }

        return true;
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