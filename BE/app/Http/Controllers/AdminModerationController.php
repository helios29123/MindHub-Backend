<?php

namespace App\Http\Controllers;

use App\Http\Requests\Moderation\ModerateItemRequest;
use App\Http\Resources\ApiResource;
use App\Services\ModerationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminModerationController extends Controller
{
    public function __construct(
        private readonly ModerationService $moderationService
    ) {
    }

    public function moderateItem(ModerateItemRequest $request, mixed $id): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Dữ liệu không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $item = $this->moderationService->moderateItem((int) $id, $request->validated());

        return ApiResponse::success(
            new ApiResource($item),
            'Thao tác thành công',
            200
        );
    }
}
