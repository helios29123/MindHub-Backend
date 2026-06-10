<?php

namespace App\Http\Controllers;

use App\Http\Requests\Interaction\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\InteractionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InteractionController extends Controller
{
    public function __construct(
        private readonly InteractionService $interactionService
    ) {
    }

    public function lessonComments(Request $request, mixed $id): JsonResponse
    {
        // 1. Validate path parameter
        $pathValidator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($pathValidator->fails()) {
            return ApiResponse::error('Dữ liệu không hợp lệ.', $pathValidator->errors()->toArray(), 422);
        }

        $lessonId = (int) $id;

        // 2. Handle GET request
        if ($request->isMethod('get')) {
            $allowedKeys = ['page', 'per_page'];
            $extraParams = array_diff(array_keys($request->query()), $allowedKeys);

            if (!empty($extraParams)) {
                return ApiResponse::error('Tham số không hợp lệ.', ['query' => 'Chứa tham số không hợp lệ ngoài whitelist.'], 422);
            }

            $queryValidator = Validator::make($request->query(), [
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            if ($queryValidator->fails()) {
                return ApiResponse::error('Dữ liệu không hợp lệ.', $queryValidator->errors()->toArray(), 422);
            }

            $commentsPaginator = $this->interactionService->getLessonComments($lessonId, $queryValidator->validated(), $request->user());

            return ApiResponse::paginated(
                CommentResource::collection($commentsPaginator),
                $commentsPaginator,
                'Lấy danh sách bình luận thành công'
            );
        }

        // 3. Handle POST request
        if ($request->isMethod('post')) {
            // Resolve validation using StoreCommentRequest
            $storeRequest = app(StoreCommentRequest::class);
            
            // Re-run validation rules on request body
            $bodyValidator = Validator::make($request->json()->all(), $storeRequest->rules());

            if ($bodyValidator->fails()) {
                return ApiResponse::error('Dữ liệu không hợp lệ.', $bodyValidator->errors()->toArray(), 422);
            }

            $comment = $this->interactionService->createComment($lessonId, $bodyValidator->validated(), $request->user());

            return ApiResponse::success(
                new CommentResource($comment),
                'Thao tác thành công',
                201
            );
        }

        return ApiResponse::error('Phương thức không được hỗ trợ.', [], 405);
    }
}
