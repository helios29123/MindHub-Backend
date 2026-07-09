<?php
namespace App\Http\Controllers;
use App\Http\Requests\Interaction\InstructorQuestionIndexRequest;
use App\Http\Requests\Interaction\InstructorQuestionLessonOptionRequest;
use App\Http\Requests\Interaction\InstructorQuestionSummaryRequest;
use App\Http\Requests\Interaction\ReplyCommentRequest;
use App\Http\Requests\Interaction\StoreCommentRequest;
use App\Http\Requests\Interaction\StoreReviewRequest;
use App\Http\Resources\Interaction\CommentReplyResource;
use App\Http\Resources\Interaction\CommentResource;
use App\Http\Resources\Interaction\InstructorQuestionCourseOptionResource;
use App\Http\Resources\Interaction\InstructorQuestionDetailResource;
use App\Http\Resources\Interaction\InstructorQuestionLessonOptionResource;
use App\Http\Resources\Interaction\InstructorQuestionResource;
use App\Http\Resources\Interaction\InstructorQuestionSummaryResource;
use App\Http\Resources\Interaction\ReviewResource;
use App\Services\Interaction\InstructorQuestionService;
use App\Services\Interaction\InteractionService;
use App\Services\Interaction\ReviewService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
class InteractionController extends Controller
{
    public function __construct(
        private readonly InteractionService $interactionService,
        private readonly ReviewService $reviewService,
        private readonly InstructorQuestionService $instructorQuestionService
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
            $commentsPaginator = $this->interactionService->getLessonComments(
                $lessonId,
                $queryValidator->validated(),
                $request->user()
            );
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
            $comment = $this->interactionService->createComment(
                $lessonId,
                $bodyValidator->validated(),
                $request->user()
            );
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
        $result = $this->instructorQuestionService->replyQuestion(
            $request->user(),
            (int) $id,
            (string) $request->validated('content')
        );
        return response()->json([
            'success' => true,
            'message' => 'Trả lời câu hỏi thành công.',
            'data' => [
                'reply' => (new CommentReplyResource($result['reply']))->resolve($request),
                'question_status' => $result['question_status'],
            ],
        ], 201);
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
    public function instructorQuestionSummary(
        InstructorQuestionSummaryRequest $request
    ): JsonResponse {
        $summary = $this->instructorQuestionService->getSummary(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Lấy tổng quan câu hỏi thành công.',
            'data' => (new InstructorQuestionSummaryResource($summary))->resolve($request),
        ]);
    }
    public function instructorQuestions(
        InstructorQuestionIndexRequest $request
    ): JsonResponse {
        $questions = $this->instructorQuestionService->paginateQuestions(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách câu hỏi thành công.',
            'data' => InstructorQuestionResource::collection($questions->items())->resolve($request),
            'meta' => [
                'current_page' => $questions->currentPage(),
                'last_page' => $questions->lastPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
            ],
        ]);
    }
    public function showInstructorQuestion(Request $request, mixed $id): JsonResponse
    {
        $detail = $this->instructorQuestionService->showQuestion(
            $request->user(),
            $id
        );
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết câu hỏi thành công.',
            'data' => (new InstructorQuestionDetailResource($detail))->resolve($request),
        ]);
    }
    public function instructorQuestionCourseOptions(Request $request): JsonResponse
    {
        $courses = $this->instructorQuestionService->getCourseOptions($request->user());
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách khóa học cho bộ lọc thành công.',
            'data' => InstructorQuestionCourseOptionResource::collection($courses)->resolve($request),
        ]);
    }
    public function instructorQuestionLessonOptions(
        InstructorQuestionLessonOptionRequest $request
    ): JsonResponse {
        $lessons = $this->instructorQuestionService->getLessonOptions(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách bài học cho bộ lọc thành công.',
            'data' => InstructorQuestionLessonOptionResource::collection($lessons)->resolve($request),
        ]);
    }
}