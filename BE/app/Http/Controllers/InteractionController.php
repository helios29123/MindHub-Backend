<?php
namespace App\Http\Controllers;
use App\Http\Requests\Interaction\ReplyCommentRequest;
use App\Http\Requests\Interaction\StoreCommentRequest;
use App\Http\Requests\Interaction\StoreReviewRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\Interaction\ReviewResource;
use App\Services\Interaction\ReviewService;
use App\Services\InteractionService;
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
        if ($request->isMethod('post')) {
            $storeRequest = app(StoreCommentRequest::class);
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
            'Thao tác thành công',
            201
        );
    }
    public function storeReview(StoreReviewRequest $request, mixed $id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return ApiResponse::error('Dữ liệu đánh giá không hợp lệ.', $validator->errors()->toArray(), 422);
        }
        try {
            $review = $this->reviewService->storeReview(
                courseId: (int) $id,
                payload: $request->validated(),
                learner: $request->user()
            );
            return ApiResponse::success(
                new ReviewResource($review),
                'Cảm ơn bạn đã đánh giá khóa học.',
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
}