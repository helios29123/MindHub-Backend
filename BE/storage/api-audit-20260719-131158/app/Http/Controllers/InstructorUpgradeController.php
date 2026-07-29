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
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $applications = $this->instructorUpgradeService->adminIndex($perPage);

        return ApiResponse::success(
            [
                'items' => InstructorUpgradeRequestResource::collection($applications->items()),
                'meta' => [
                    'current_page' => $applications->currentPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                    'last_page' => $applications->lastPage(),
                ],
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

    public function reject(int $userId): JsonResponse
    {
        $application = $this->instructorUpgradeService->reject($userId);

        return ApiResponse::success(
            new InstructorUpgradeRequestResource($application),
            'Từ chối yêu cầu nâng cấp giảng viên thành công.'
        );
    }
}
