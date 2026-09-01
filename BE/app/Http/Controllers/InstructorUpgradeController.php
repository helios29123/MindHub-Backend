<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\StoreInstructorUpgradeRequest;
use App\Http\Requests\Instructor\UpdateInstructorUpgradeRequest;
use App\Http\Resources\Instructor\InstructorUpgradeRequestResource;
use App\Services\Instructor\InstructorUpgradeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorUpgradeController extends Controller
{
    public function __construct(
        private readonly InstructorUpgradeService $instructorUpgradeService
    ) {
    }

    public function myApplication(Request $request): JsonResponse
    {
        $application = $this->instructorUpgradeService->myApplication($request->user());

        return ApiResponse::success(
            new InstructorUpgradeRequestResource($application),
            'Lấy trạng thái yêu cầu nâng cấp giảng viên thành công.'
        );
    }

    public function store(StoreInstructorUpgradeRequest $request): JsonResponse
    {
        $application = $this->instructorUpgradeService->store(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorUpgradeRequestResource($application),
            'Gửi yêu cầu nâng cấp lên giảng viên thành công. Vui lòng chờ admin duyệt.',
            201
        );
    }

    public function update(UpdateInstructorUpgradeRequest $request): JsonResponse
    {
        $application = $this->instructorUpgradeService->update(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorUpgradeRequestResource($application),
            'Cập nhật và gửi lại yêu cầu nâng cấp giảng viên thành công.'
        );
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $result = $this->instructorUpgradeService->adminIndexReport($request->all());

        return ApiResponse::success(
            [
                'summary' => $result['summary'],
                'items' => InstructorUpgradeRequestResource::collection($result['items']),
                'meta' => $result['meta'],
            ],
            'Lấy danh sách yêu cầu nâng cấp giảng viên thành công.'
        );
    }

    public function adminShow(int $userId): JsonResponse
    {
        $application = $this->instructorUpgradeService->adminShow($userId);

        return ApiResponse::success(
            new InstructorUpgradeRequestResource($application),
            'Lấy chi tiết yêu cầu nâng cấp giảng viên thành công.'
        );
    }

    public function approve(int $userId): JsonResponse
    {
        $application = $this->instructorUpgradeService->approve($userId);

        return ApiResponse::success(
            new InstructorUpgradeRequestResource($application),
            'Duyệt nâng cấp giảng viên thành công.'
        );
    }

    public function reject(Request $request, int $userId): JsonResponse
    {
        $reason = $request->input('reason') ?? $request->input('rejection_reason');
        if (empty($reason) || ! is_string($reason) || trim($reason) === '') {
            throw new \App\Exceptions\BusinessException('Vui lòng nhập lý do từ chối yêu cầu.', 422);
        }

        $application = $this->instructorUpgradeService->reject($userId, trim($reason));

        return ApiResponse::success(
            new InstructorUpgradeRequestResource($application),
            'Từ chối yêu cầu nâng cấp giảng viên thành công.'
        );
    }
}
