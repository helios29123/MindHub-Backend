<?php

namespace App\Http\Controllers;

use App\Services\User\UserActivityService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserActivityController extends Controller
{
    public function __construct(
        private readonly UserActivityService $userActivityService
    ) {
    }

    public function getActivityDashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $this->userActivityService->getActivityDashboardData($user);

        return ApiResponse::success(
            data: $data,
            message: 'Lấy dữ liệu hoạt động thành công'
        );
    }
}
