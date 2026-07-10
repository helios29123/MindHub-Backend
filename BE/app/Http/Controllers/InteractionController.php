<?php
namespace App\Http\Controllers;

use App\Services\Interaction\InstructorQuestionService;
use App\Http\Resources\Interaction\InstructorQuestionResource;
use App\Http\Requests\Interaction\InstructorQuestionQueryRequest;
use App\Http\Requests\Interaction\ReplyCommentRequest;
use App\Http\Requests\Interaction\StoreCommentRequest;
use App\Http\Requests\Interaction\StoreReviewRequest;
use App\Http\Resources\Interaction\CommentResource;
use App\Http\Resources\Interaction\ReviewResource;
use App\Services\Interaction\ReviewService;
use App\Services\Interaction\InteractionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
class InteractionController extends Controller
{
    public function __construct(
        private readonly InteractionService $interactionService,
        private readonly ReviewService $reviewService
    ) {
    }
    public function lessonComments(Request $request, mixed $id): JsonResponse
    {
        $pathValidator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);
        if ($pathValidator->fails()) {
            return ApiResponse::error('Dữ liệu không hợp lệ.', $pathValidator->errors()->toArray(), 422);
        }
        $lessonId = (int) $id;
        if ($request->isMethod('get')) {
            $allowedKeys = ['page', 'per_page'];
            $extraParams = array_diff(array_keys($request->query()), $allowedKeys);
            if (!empty($extraParams)) {
                return ApiResponse::error('Dữ liệu không hợp lệ.', ['query' => 'Dữ liệu không hợp lệ.'], 422);
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
                'Dữ liệu không hợp lệ.'
            );
        }
        if ($request->isMethod('post')) {
            $storeRequest = app(StoreCommentRequest::class);
            $bodyValidator = Validator::make($request->json()->all(), $storeRequest->rules());
            if ($bodyValidator->fails()) {
                return ApiResponse::error('Dữ liệu không hợp lệ.', $bodyValidator->errors()->toArray(), 422);
            }
            $comment = $this->interactionService->createComment($lessonId, $bodyValidator->validated(), $request->user());
            return ApiResponse::success(
                new CommentResource($comment),
                'Dữ liệu không hợp lệ.',
                201
            );
        }
        return ApiResponse::error('Dữ liệu không hợp lệ.', [], 405);
    }
    public function replyComment(ReplyCommentRequest $request, mixed $id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return ApiResponse::error('Dữ liệu không hợp lệ.', $validator->errors()->toArray(), 422);
        }
        $reply = $this->interactionService->replyToComment((int) $id, $request->validated(), $request->user());
        return ApiResponse::success(
            new CommentResource($reply),
            'Dữ liệu không hợp lệ.',
            201
        );
    }
    public function storeReview(StoreReviewRequest $request, mixed $id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return ApiResponse::error('Dữ liệu không hợp lệ.', $validator->errors()->toArray(), 422);
        }
        try {
            $review = $this->reviewService->storeReview(
                courseId: (int) $id,
                payload: $request->validated(),
                learner: $request->user()
            );
            return ApiResponse::success(
                new ReviewResource($review),
                'Dữ liệu không hợp lệ.',
                201
            );
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                [],
                $exception->getStatusCode()
            );
        }
    }

public function instructorQuestions(InstructorQuestionQueryRequest $request): JsonResponse
    {
        $paginator = app(InstructorQuestionService::class)->paginateQuestions(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            InstructorQuestionResource::collection(collect($paginator->items()))->resolve($request),
            'Dữ liệu không hợp lệ.',
            200,
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }
}