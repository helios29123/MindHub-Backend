<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quiz\StoreQuizAttemptRequest;
use App\Http\Resources\QuizAttemptResource;
use App\Services\QuizService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    public function __construct(
        private readonly QuizService $quizService
    ) {
    }

    public function storeAttempt(StoreQuizAttemptRequest $request, mixed $id): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Dữ liệu không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $attempt = $this->quizService->storeAttempt((int) $id, $request->validated(), $request->user());

        return ApiResponse::success(
            new QuizAttemptResource($attempt),
            'Thao tác thành công',
            201
        );
    }
}
