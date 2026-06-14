<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quiz\StoreQuizAttemptRequest;
use App\Http\Requests\Quiz\CompletionStatusRequest;
use App\Http\Resources\Quiz\QuizAttemptResource;
use App\Http\Resources\Quiz\CompletionStatusResource;
use App\Services\Quiz\QuizService;
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
            return ApiResponse::error('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', $validator->errors()->toArray(), 422);
        }

        $attempt = $this->quizService->storeAttempt((int) $id, $request->validated(), $request->user());

        return ApiResponse::success(
            new QuizAttemptResource($attempt),
            'Thao tﾃ｡c thﾃnh cﾃｴng',
            201
        );
    }

    public function completionStatus(CompletionStatusRequest $request, mixed $id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Dữ liệu không hợp lệ.',
                $validator->errors()->toArray(),
                422
            );
        }

        $status = $this->quizService->completionStatus(
            (int) $id,
            $request->validated(),
            $request->user()
        );

        return ApiResponse::success(
            new CompletionStatusResource($status),
            'Thao tác thành công',
            200
        );
    }
}
