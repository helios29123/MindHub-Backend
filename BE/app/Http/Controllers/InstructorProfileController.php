<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\InstructorAccountUpdateRequest;
use App\Http\Requests\Instructor\InstructorExpertiseUpdateRequest;
use App\Http\Requests\Instructor\InstructorIntroductionUpdateRequest;
use App\Http\Resources\Instructor\InstructorAccountResource;
use App\Http\Resources\Instructor\InstructorProfileCompletionResource;
use App\Http\Resources\Instructor\InstructorProfileResource;
use App\Http\Resources\Instructor\InstructorProfileSectionResource;
use App\Services\Instructor\InstructorProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InstructorProfileController extends Controller
{
    public function __construct(
        private readonly InstructorProfileService $service
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $result = $this->service->getProfile($request->user());

        return ApiResponse::success(
            new InstructorProfileResource($result),
            'Lấy hồ sơ giảng viên thành công.'
        );
    }

    public function updateAccount(
        InstructorAccountUpdateRequest $request
    ): JsonResponse {
        $user = $this->service->updateAccount(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorAccountResource($user),
            'Cập nhật thông tin tài khoản thành công.'
        );
    }

    public function updateIntroduction(
        InstructorIntroductionUpdateRequest $request
    ): JsonResponse {
        $result = $this->service->updateIntroduction(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorProfileSectionResource($result),
            'Cập nhật giới thiệu giảng viên thành công.'
        );
    }

    public function updateExpertise(
        InstructorExpertiseUpdateRequest $request
    ): JsonResponse {
        $result = $this->service->updateExpertise(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorProfileSectionResource($result),
            'Cập nhật chuyên môn giảng viên thành công.'
        );
    }

    public function completion(Request $request): JsonResponse
    {
        $completion = $this->service->getCompletion($request->user());

        return ApiResponse::success(
            new InstructorProfileCompletionResource($completion),
            'Lấy trạng thái hoàn thiện hồ sơ thành công.'
        );
    }
}