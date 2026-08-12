<?php
namespace App\Http\Controllers;

use App\Services\Interaction\InstructorQuestionService;
use App\Http\Resources\Interaction\InstructorQuestionResource;
use App\Http\Resources\Interaction\InstructorQuestionSummaryResource;
use App\Http\Resources\Interaction\InstructorQuestionCourseOptionResource;
use App\Http\Resources\Interaction\InstructorQuestionLessonOptionResource;
use App\Http\Resources\Interaction\InstructorQuestionDetailResource;
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
        return ApiResponse::error('Dữ liệu không hợp lệ.', [], 405);
    }
    public function replyComment(Request $request, mixed $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Dữ liệu không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        try {
            $result = app(InstructorQuestionService::class)->replyToQuestion(
                (int) $request->user()->id,
                (int) $id,
                $request->all()
            );

            return ApiResponse::success([
                'comment_id' => $result['reply']->id,
                'status' => $result['reply']->status ?? 'visible',
                'reply' => [
                    'id' => $result['reply']->id,
                    'parent_id' => $result['reply']->parent_id,
                    'lesson_id' => $result['reply']->lesson_id,
                    'content' => $result['reply']->content,
                ],
                'question_status' => $result['question_status']
            ], 'Thao tác thành công', 201);
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
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
        try {
            $paginator = app(InstructorQuestionService::class)->paginateQuestions(
                (int) $request->user()->id,
                $request->validated()
            );

            return ApiResponse::success(
                InstructorQuestionResource::collection(collect($paginator->items()))->resolve($request),
                'Lấy danh sách câu hỏi thành công.',
                200,
                [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ]
            );
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function instructorQuestionSummary(Request $request): JsonResponse
    {
        try {
            $summary = app(InstructorQuestionService::class)->getQuestionSummary(
                (int) $request->user()->id,
                $request->all()
            );

            return ApiResponse::success(
                new InstructorQuestionSummaryResource((object) $summary),
                'Lấy thống kê câu hỏi thành công.'
            );
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function instructorQuestionCourseOptions(Request $request): JsonResponse
    {
        $courses = \Illuminate\Support\Facades\DB::table('courses')
            ->where('instructor_id', $request->user()->id)
            ->whereNull('deleted_at')
            ->select('id', 'title')
            ->when($request->query('search'), function ($query, $search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->get();

        return ApiResponse::success(
            InstructorQuestionCourseOptionResource::collection($courses)->resolve($request),
            'Lấy danh sách khóa học thành công.'
        );
    }

    public function instructorQuestionLessonOptions(Request $request): JsonResponse
    {
        try {
            $instructorId = (int) $request->user()->id;
            $courseId = $request->query('course_id');
            if ($courseId) {
                $owns = \Illuminate\Support\Facades\DB::table('courses')
                    ->where('id', $courseId)
                    ->where('instructor_id', $instructorId)
                    ->whereNull('deleted_at')
                    ->exists();
                if (!$owns) {
                    return ApiResponse::error('Khoá học không hợp lệ hoặc không thuộc về giảng viên.', [], 422);
                }
            }

            $lessons = \Illuminate\Support\Facades\DB::table('lessons')
                ->join('courses', 'courses.id', '=', 'lessons.course_id')
                ->where('courses.instructor_id', $instructorId)
                ->whereNull('courses.deleted_at')
                ->select('lessons.id', 'lessons.title')
                ->when($courseId, function ($query, $courseId) {
                    $query->where('lessons.course_id', $courseId);
                })
                ->when($request->query('search'), function ($query, $search) {
                    $query->where('lessons.title', 'like', '%' . $search . '%');
                })
                ->get();

            return ApiResponse::success(
                InstructorQuestionLessonOptionResource::collection($lessons)->resolve($request),
                'Lấy danh sách bài học thành công.'
            );
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function showInstructorQuestion(Request $request, mixed $id): JsonResponse
    {
        try {
            $comment = app(InstructorQuestionService::class)->getQuestionDetails(
                (int) $request->user()->id,
                (int) $id
            );

            $instructorId = (int) $request->user()->id;
            $isAnswered = \App\Models\Comment::where('parent_id', $comment->id)
                ->where('user_id', $instructorId)
                ->where('status', 'visible')
                ->exists();

            $questionData = [
                'comment_id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at?->toDateTimeString() ?? $comment->created_at,
                'learner_id' => $comment->user_id,
                'learner_full_name' => $comment->user?->full_name,
                'learner_email' => $comment->user?->email,
                'lesson_id' => $comment->lesson_id,
                'lesson_title' => $comment->lesson?->title,
                'course_id' => $comment->lesson?->course_id,
                'course_title' => $comment->lesson?->course?->title,
            ];

            $replies = \App\Models\Comment::where('parent_id', $comment->id)
                ->where('status', 'visible')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'parent_id' => $reply->parent_id,
                        'lesson_id' => $reply->lesson_id,
                        'content' => $reply->content,
                        'status' => $reply->status,
                        'created_at' => $reply->created_at?->toDateTimeString() ?? $reply->created_at,
                        'user_id' => $reply->user_id,
                        'user_full_name' => $reply->user?->full_name,
                        'user_role' => $reply->user?->role,
                    ];
                })
                ->all();

            $resourceData = [
                'question' => $questionData,
                'replies' => $replies,
                'is_answered' => $isAnswered,
            ];

            return ApiResponse::success(
                (new InstructorQuestionDetailResource((object) $resourceData))->resolve($request),
                'Lấy chi tiết câu hỏi thành công.'
            );
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function replyInstructorQuestion(Request $request, mixed $id): JsonResponse
    {
        return $this->replyComment($request, $id);
    }

    public function hideInstructorQuestion(Request $request, mixed $id): JsonResponse
    {
        try {
            $comment = app(InstructorQuestionService::class)->hideQuestion(
                (int) $request->user()->id,
                (int) $id
            );

            return ApiResponse::success(null, 'Ẩn câu hỏi thành công.');
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function showHiddenInstructorQuestion(Request $request, mixed $id): JsonResponse
    {
        try {
            $comment = app(InstructorQuestionService::class)->showQuestion(
                (int) $request->user()->id,
                (int) $id
            );

            return ApiResponse::success(null, 'Hiển thị câu hỏi thành công.');
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function deleteInstructorQuestion(Request $request, mixed $id): JsonResponse
    {
        try {
            $comment = app(InstructorQuestionService::class)->deleteQuestion(
                (int) $request->user()->id,
                (int) $id
            );

            return ApiResponse::success(null, 'Xóa câu hỏi thành công.');
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function starInstructorQuestion(Request $request, mixed $id): JsonResponse
    {
        try {
            $res = app(InstructorQuestionService::class)->starQuestion(
                (int) $request->user()->id,
                (int) $id
            );

            return ApiResponse::success($res, 'Đánh dấu câu hỏi thành công.');
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function unstarInstructorQuestion(Request $request, mixed $id): JsonResponse
    {
        try {
            $res = app(InstructorQuestionService::class)->unstarQuestion(
                (int) $request->user()->id,
                (int) $id
            );

            return ApiResponse::success($res, 'Bỏ đánh dấu câu hỏi thành công.');
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function updateInstructorQuestionStatus(Request $request, mixed $id): JsonResponse
    {
        try {
            $status = $request->input('status', 'answered');
            $comment = app(InstructorQuestionService::class)->updateQuestionStatus(
                (int) $request->user()->id,
                (int) $id,
                $status
            );

            return ApiResponse::success(['status' => $comment->status], 'Cập nhật trạng thái thành công.');
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function updateInstructorQuestionReply(Request $request, mixed $id, mixed $replyId): JsonResponse
    {
        try {
            $reply = app(InstructorQuestionService::class)->updateReply(
                (int) $request->user()->id,
                (int) $id,
                (int) $replyId,
                $request->all()
            );

            return ApiResponse::success(['id' => $reply->id, 'content' => $reply->content], 'Cập nhật câu trả lời thành công.');
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function deleteInstructorQuestionReply(Request $request, mixed $id, mixed $replyId): JsonResponse
    {
        try {
            $res = app(InstructorQuestionService::class)->deleteReply(
                (int) $request->user()->id,
                (int) $id,
                (int) $replyId
            );

            return ApiResponse::success($res, 'Xóa câu trả lời thành công.');
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }
}
