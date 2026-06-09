<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserProfileRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Hash;

final class UserProfileService
{
    public function __construct(
        private readonly UserProfileRepository $userProfileRepository
    ) {}

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
    public function changePassword(
        Authenticatable $authenticatedUser,
        array $validatedData
    ): void {
        DB::transaction(function () use ($authenticatedUser, $validatedData): void {
            $userId = (int) $authenticatedUser->getAuthIdentifier();

            $user = $this->userProfileRepository->findPasswordCredentialById($userId);
            if (! Hash::check($validatedData['current_password'], $user->password_hash)) {
                throw new BusinessException(
                    'Mật khẩu hiện tại không đúng.',
                    400,
                    []
                );
            }

            $this->userProfileRepository->updatePasswordById(
                $userId,
                Hash::make($validatedData['password'])
            );
        });
    }
}
