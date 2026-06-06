<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserProfileRepository;
use Illuminate\Contracts\Auth\Authenticatable;

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
}