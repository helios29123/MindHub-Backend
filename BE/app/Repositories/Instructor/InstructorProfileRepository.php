<?php

namespace App\Repositories\Instructor;

use App\Models\InstructorProfile;
use App\Models\User;

final class InstructorProfileRepository
{
    public function findInstructorUser(int $userId): ?User
    {
        return User::query()
            ->where('id', $userId)
            ->where('role', User::ROLE_INSTRUCTOR)
            ->whereNull('deleted_at')
            ->first();
    }

    public function create(array $data): \App\Models\InstructorProfile
    {
        return \App\Models\InstructorProfile::create($data);
    }

    public function findProfileByUserId(int $userId): ?InstructorProfile
    {
        return InstructorProfile::query()
            ->where('user_id', $userId)
            ->first();
    }

    public function updateAccount(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user->refresh();
    }

    public function updateOrCreateProfile(
        int $userId,
        array $data
    ): InstructorProfile {
        return InstructorProfile::query()->updateOrCreate(
            ['user_id' => $userId],
            $data
        )->refresh();
    }
}
