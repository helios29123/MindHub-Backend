<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\MeProfileRequest;
use App\Http\Requests\User\UpdateMeRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileService $userProfileService
    ) {
    }

    public function me(MeProfileRequest $request): JsonResponse
    {
        $user = $this->userProfileService->getAuthenticatedProfile(
            $request->user()
        );

        return ApiResponse::success(
            data: new UserResource($user),
            message: 'Lấy dữ liệu thành công'
        );
    }

    public function updateMe(UpdateMeRequest $request): JsonResponse
    {
        $user = $this->userProfileService->updateAuthenticatedProfile(
            authenticatedUser: $request->user(),
            validatedData: $request->validated()
        );

        return ApiResponse::success(
            data: new UserResource($user),
            message: 'Thao tác thành công'
        );
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'avatar.required' => 'Vui lòng chọn file ảnh đại diện.',
            'avatar.image' => 'File tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện phải thuộc định dạng: JPG, PNG hoặc WEBP.',
            'avatar.max' => 'Dung lượng ảnh tối đa là 5MB.',
        ]);

        $avatarUrl = $this->userProfileService->uploadAvatar(
            $request->user(),
            $request->file('avatar')
        );

        return ApiResponse::success([
            'avatar' => $avatarUrl,
            'avatar_url' => $avatarUrl,
        ], 'Tải ảnh đại diện thành công.');
    }

    public function selectAvatarPreset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preset_id' => 'required|string',
        ], [
            'preset_id.required' => 'Vui lòng chọn ảnh đại diện mẫu.',
        ]);

        $avatarUrl = $this->userProfileService->selectAvatarPreset(
            $request->user(),
            $validated['preset_id']
        );

        return ApiResponse::success([
            'avatar' => $avatarUrl,
            'avatar_url' => $avatarUrl,
        ], 'Cập nhật ảnh đại diện mẫu thành công.');
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $this->userProfileService->deleteAvatar($request->user());

        return ApiResponse::success([
            'avatar' => null,
            'avatar_url' => null,
        ], 'Đã xóa ảnh đại diện.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->userProfileService->changePassword(
                $request->user(),
                $request->validated()
            );

            return ApiResponse::success(
                data: [],
                message: 'Đổi mật khẩu thành công.'
            );
        } catch (BusinessException $exception) {
            return ApiResponse::error(
                message: $exception->getMessage(),
                errors: $exception->getErrors(),
                status: $exception->getCode() > 0 ? $exception->getCode() : 400
            );
        }
    }
}
