<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserProfileRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class UserProfileService
{
    public function __construct(
        private readonly UserProfileRepository $userProfileRepository
    ) {
    }

    public function getAuthenticatedProfile(Authenticatable $authenticatedUser): User
    {
        return $this->userProfileRepository->findPublicProfileById(
            id: (int) $authenticatedUser->getAuthIdentifier()
        );
    }

    public function updateAuthenticatedProfile(
        Authenticatable $authenticatedUser,
        array $validatedData
    ): User {
        return DB::transaction(function () use ($authenticatedUser, $validatedData): User {
            $userId = (int) $authenticatedUser->getAuthIdentifier();

            $this->userProfileRepository->updateProfileById(
                id: $userId,
                data: $validatedData
            );

            return $this->userProfileRepository->findPublicProfileById(
                id: $userId
            );
        });
    }
}