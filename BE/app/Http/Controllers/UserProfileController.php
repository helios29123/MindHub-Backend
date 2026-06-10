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
