<?php

namespace App\Services\User;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\User\UserProfileRepository;
use App\Services\Storage\CloudinaryService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UserProfileService
{
    public function __construct(
        private readonly UserProfileRepository $userProfileRepository,
        private readonly CloudinaryService $cloudinaryService,
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

    public function uploadAvatar(
        Authenticatable $authenticatedUser,
        UploadedFile $file
    ): string {
        $userId = (int) $authenticatedUser->getAuthIdentifier();

        $user = User::query()->findOrFail($userId);

        // Upload new avatar first.
        $uploaded = $this->cloudinaryService->uploadImage(
            $file,
            'mindhub/avatars'
        );

        $oldPublicId = $user->avatar_public_id;

        $user->avatar_url = $uploaded['url'];
        $user->avatar_public_id = $uploaded['public_id'];
        $user->save();

        // Delete old Cloudinary asset only after the new one is persisted.
        if (!empty($oldPublicId)) {
            $this->cloudinaryService->deleteImage($oldPublicId);
        }

        return $uploaded['url'];
    }

    public function selectAvatarPreset(
        Authenticatable $authenticatedUser,
        string $presetId
    ): string {
        $userId = (int) $authenticatedUser->getAuthIdentifier();

        $user = User::query()->findOrFail($userId);

        $presets = [
            'avatar_01' => 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?: 'MindHub User') . '&background=007A64&color=fff&bold=true',
            'avatar_02' => 'https://ui-avatars.com/api/?name=Instructor&background=121b4b&color=fff&bold=true',
            'avatar_03' => 'https://ui-avatars.com/api/?name=Student&background=0284c7&color=fff&bold=true',
            'avatar_04' => 'https://ui-avatars.com/api/?name=Learner&background=7c3aed&color=fff&bold=true',
            'avatar_05' => 'https://ui-avatars.com/api/?name=Pro&background=d97706&color=fff&bold=true',
        ];

        if (!isset($presets[$presetId])) {
            throw new BusinessException(
                'Mẫu ảnh đại diện không hợp lệ.',
                422
            );
        }

        $oldPublicId = $user->avatar_public_id;
        $avatarUrl = $presets[$presetId];

        $user->avatar_url = $avatarUrl;
        $user->avatar_public_id = null;
        $user->save();

        if (!empty($oldPublicId)) {
            $this->cloudinaryService->deleteImage($oldPublicId);
        }

        return $avatarUrl;
    }

    public function deleteAvatar(
        Authenticatable $authenticatedUser
    ): void {
        $userId = (int) $authenticatedUser->getAuthIdentifier();

        $user = User::query()->findOrFail($userId);

        $oldPublicId = $user->avatar_public_id;

        if (!empty($oldPublicId)) {
            $this->cloudinaryService->deleteImage($oldPublicId);
        }

        $user->avatar_url = null;
        $user->avatar_public_id = null;
        $user->save();
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
