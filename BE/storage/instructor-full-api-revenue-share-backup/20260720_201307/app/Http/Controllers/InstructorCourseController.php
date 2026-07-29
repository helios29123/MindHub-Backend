<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\InstructorCourseIndexRequest;
use App\Http\Requests\Instructor\InstructorCourseDraftRequest;
use App\Http\Requests\Instructor\InstructorLearnerIndexRequest;
use App\Http\Requests\Instructor\InstructorWithdrawIndexRequest;
use App\Http\Requests\Instructor\InstructorWithdrawSummaryRequest;
use App\Http\Resources\Instructor\InstructorLearnerResource;
use App\Http\Resources\Instructor\InstructorWithdrawResource;
use App\Http\Resources\Instructor\InstructorWithdrawSummaryResource;
use App\Services\Instructor\InstructorLearnerService;
use App\Services\Instructor\InstructorWithdrawalService;
use Illuminate\Http\JsonResponse;
use App\Support\ApiResponse;
use App\Exceptions\BusinessException;
use App\Http\Requests\Instructor\InstructorRevenueQueryRequest;
use App\Http\Requests\Instructor\ManageLessonsRequest;
use App\Http\Requests\Instructor\SectionQueryRequest;
use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Requests\Instructor\StoreLessonRequest;
use App\Http\Requests\Instructor\StoreSectionRequest;
use App\Http\Requests\Instructor\SubmitForReviewRequest;
use App\Http\Requests\Instructor\TogglePreviewRequest;
use App\Http\Requests\Instructor\UpdateCourseRequest;
use App\Http\Requests\Instructor\UpdateLessonRequest;
use App\Http\Requests\Instructor\UpdateSectionRequest;
use App\Http\Requests\Instructor\UploadLessonAssetRequest;
use App\Http\Requests\Instructor\UploadLessonVideoRequest;
use App\Http\Requests\Instructor\WithdrawRequest as InstructorWithdrawRequest;
use App\Http\Resources\Instructor\InstructorCourseResource;
use App\Http\Resources\Instructor\InstructorCourseDetailResource;
use App\Http\Resources\Instructor\InstructorCourseContentResource;
use App\Http\Resources\Instructor\InstructorQuizResource;
use App\Http\Resources\Instructor\InstructorRevenueResource;
use App\Http\Requests\Instructor\CourseLearnerQueryRequest;
use App\Http\Resources\Instructor\CourseLearnerResource;
use App\Http\Resources\Instructor\InstructorSectionResource;
use App\Http\Resources\Instructor\LessonAssetResource;
use App\Http\Resources\Instructor\LessonResource;
use App\Http\Resources\Instructor\ReviewNoteResource;
use App\Http\Resources\Instructor\WithdrawRequestResource;
use App\Services\Instructor\InstructorCourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class InstructorCourseController extends Controller
{
    public function __construct(
        private readonly InstructorCourseService $instructorCourseService,
    ) {
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->instructorCourseService->createCourse(
            $request->user(),
            $request->validated(),
        );

        return ApiResponse::success(
            new InstructorCourseResource($course),
            "Thao tác thành công.",
            201,
        );
    }

    public function indexLessons(ManageLessonsRequest $request): JsonResponse
    {
        $lessons = $this->instructorCourseService->paginateLessons(
            $request->user(),
            $request->validated(),
        );

        return ApiResponse::paginated(
            LessonResource::collection($lessons),
            $lessons,
            "Thao tác thành công.",
        );
    }

    public function storeLesson(StoreLessonRequest $request): JsonResponse
    {
        $lesson = $this->instructorCourseService->createLesson(
            $request->user(),
            $request->validated(),
        );

        return ApiResponse::success(
            new LessonResource($lesson),
            "Thao tác thành công.",
            201,
        );
    }

    public function showLesson(int $id): JsonResponse
    {
        $lesson = $this->instructorCourseService->getLesson(
            request()->user(),
            $id,
        );

        return ApiResponse::success(
            new LessonResource($lesson),
            "Thao tác thành công.",
        );
    }

    public function updateLesson(
        UpdateLessonRequest $request,
        int $id,
    ): JsonResponse {
        $lesson = $this->instructorCourseService->updateLesson(
            $request->user(),
            $id,
            $request->validated(),
        );

        return ApiResponse::success(
            new LessonResource($lesson),
            "Thao tác thành công.",
        );
    }

    public function togglePreview(
        TogglePreviewRequest $request,
        int $id,
    ): JsonResponse {
        $validatedData = $request->validated();

        $lesson = $this->instructorCourseService->toggleLessonPreview(
            $request->user(),
            $id,
            (bool) $validatedData["is_preview"],
        );

        return ApiResponse::success(
            new LessonResource($lesson),
            "Thao tác thành công.",
        );
    }

    public function destroyLesson(int $id): JsonResponse
    {
        $this->instructorCourseService->deleteLesson(request()->user(), $id);

        return ApiResponse::success(
            [],
            "Thao tác thành công.",
        );
    }

    public function uploadVideo(
        UploadLessonVideoRequest $request,
        int $id,
    ): JsonResponse {
        $lesson = $this->instructorCourseService->uploadLessonVideo(
            $request->user(),
            $id,
            $request->validated(),
            $request->file("video"),
        );

        return ApiResponse::success(
            new LessonResource($lesson),
            "Thao tác thành công.",
            201,
        );
    }

    public function uploadAsset(
        UploadLessonAssetRequest $request,
        int $id,
    ): JsonResponse {
        $asset = $this->instructorCourseService->uploadLessonAsset(
            $request->user(),
            $id,
            $request->validated(),
            $request->file("file"),
        );

        return ApiResponse::success(
            new LessonAssetResource($asset),
            "Thao tác thành công.",
            201,
        );
    }

    public function submitForReview(
        SubmitForReviewRequest $request,
        int $id,
    ): JsonResponse {
        $course = $this->instructorCourseService->submitForReview(
            $request->user(),
            $id,
        );

        return ApiResponse::success(
            new InstructorCourseResource($course),
            "Thao tác thành công.",
            201,
        );
    }

    public function reviewNotes(string $id): JsonResponse
    {
        if (!ctype_digit($id) || (int) $id < 1) {
            throw new BusinessException(
                "Thao tác thành công.",
                422,
            );
        }

        $course = $this->instructorCourseService->getRejectedReviewNotes(
            request()->user(),
            (int) $id,
        );

        return ApiResponse::success(
            new ReviewNoteResource($course),
            "Thao tác thành công.",
        );
    }


    public function storeDraft(InstructorCourseDraftRequest $request): JsonResponse
    {
        $course = $this->instructorCourseService->createDraftCourse(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorCourseResource($course),
            'Lưu nháp khóa học thành công.',
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $course = $this->instructorCourseService->getCourseDetail(
            $request->user(),
            $id
        );

        return ApiResponse::success(
            new InstructorCourseDetailResource($course),
            'Lấy chi tiết khóa học thành công.',
            200
        );
    }

    public function content(Request $request, int $id): JsonResponse
    {
        $course = $this->instructorCourseService->getCourseContent(
            $request->user(),
            $id
        );

        return ApiResponse::success(
            new InstructorCourseContentResource($course),
            'Lấy nội dung khóa học thành công.',
            200
        );
    }

    public function updateDraft(InstructorCourseDraftRequest $request, int $id): JsonResponse
    {
        $course = $this->instructorCourseService->updateCourseDraft(
            $request->user(),
            $id,
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorCourseResource($course),
            'Lưu nháp khóa học thành công.',
            200
        );
    }
    public function update(UpdateCourseRequest $request, int $id): JsonResponse
    {
        $course = $this->instructorCourseService->updateCourse(
            $id,
            $request->user()->id,
            $request->validated(),
        );

        return ApiResponse::success(
            new InstructorCourseResource($course),
            "Thao tác thành công.",
            200,
        );
    }

    public function sections(SectionQueryRequest $request): JsonResponse
    {
        $sections = $this->instructorCourseService->getSections(
            $request->validated(),
            $request->user()->id,
        );

        return ApiResponse::paginated(
            InstructorSectionResource::collection($sections),
            $sections,
            "Thao tác thành công.",
        );
    }

    public function showSection(int $id, Request $request): JsonResponse
    {
        $section = $this->instructorCourseService->getSection(
            $id,
            $request->user()->id,
        );

        return ApiResponse::success(
            new InstructorSectionResource($section),
            "Thao tác thành công.",
            200,
        );
    }

    public function storeSection(StoreSectionRequest $request): JsonResponse
    {
        $section = $this->instructorCourseService->createSection(
            $request->validated(),
            $request->user()->id,
        );

        return ApiResponse::success(
            new InstructorSectionResource($section),
            "Thao tác thành công.",
            201,
        );
    }

    public function updateSection(
        UpdateSectionRequest $request,
        int $id,
    ): JsonResponse {
        $section = $this->instructorCourseService->updateSection(
            $id,
            $request->validated(),
            $request->user()->id,
        );

        return ApiResponse::success(
            new InstructorSectionResource($section),
            "Thao tác thành công.",
            200,
        );
    }

    public function deleteSection(Request $request, int $id): JsonResponse
    {
        $this->instructorCourseService->deleteSection(
            $id,
            $request->user()->id,
        );

        return ApiResponse::success(
            null,
            "Thao tác thành công.",
            200,
        );
    }

    public function profile(Request $request): JsonResponse
    {
        $profile = $this->instructorCourseService->getInstructorProfile(
            $request->user()->id,
        );

        return ApiResponse::success(
            new \App\Http\Resources\Instructor\InstructorProfileResource($profile),
            "Thao tác thành công.",
            200,
        );
    }

    public function updateProfile(
        \App\Http\Requests\Instructor\UpdateInstructorProfileRequest $request,
    ): JsonResponse {
        $profile = $this->instructorCourseService->updateInstructorProfile(
            $request->user()->id,
            $request->validated(),
        );

        return ApiResponse::success(
            new \App\Http\Resources\Instructor\InstructorProfileResource($profile),
            "Thao tác thành công.",
            200,
        );
    }

    public function revenue(InstructorRevenueQueryRequest $request): JsonResponse
    {
        $report = $this->instructorCourseService->getRevenueReport(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            "success" => true,
            "message" => "Thao tác thành công.",
            "data" => (new InstructorRevenueResource($report))->resolve($request),
        ], 200);
    }

    public function manageQuizzes(Request $request, mixed $id = null): JsonResponse
    {
        $method = strtoupper($request->method());
        $quizId = $id !== null ? $this->validateInstructorQuizId($id) : null;

        if ($method === "GET") {
            if ($quizId !== null) {
                $quiz = $this->instructorCourseService->getInstructorQuiz(
                    $request->user(),
                    $quizId,
                );

                return ApiResponse::success(
                    new InstructorQuizResource($quiz),
                    "Thao tác thành công.",
                    200,
                );
            }

            $filters = $this->validateInstructorQuizInput($request->query(), [
                "page" => ["sometimes", "integer", "min:1"],
                "per_page" => ["sometimes", "integer", "min:1", "max:100"],
                "course_id" => ["sometimes", "integer", "exists:courses,id"],
                "lesson_id" => ["sometimes", "integer", "exists:lessons,id"],
                "status" => ["sometimes", "string", "in:draft,published,hidden"],
            ]);

            $quizzes = $this->instructorCourseService->paginateInstructorQuizzes(
                $request->user(),
                $filters,
            );

            return ApiResponse::paginated(
                InstructorQuizResource::collection($quizzes),
                $quizzes,
                "Thao tác thành công.",
            );
        }

        if ($method === "POST") {
            $data = $this->validateInstructorQuizInput($request->all(), [
                "lesson_id" => ["required", "integer", "exists:lessons,id"],
                "title" => ["required", "string", "max:255"],
                "description" => ["nullable", "string"],
                "passing_score" => ["nullable", "numeric", "min:0"],
                "status" => ["sometimes", "string", "in:draft,published,hidden"],
                "questions" => ["required", "array", "min:1"],
                "questions.*.question_text" => ["required", "string"],
                "questions.*.question_type" => [
                    "required",
                    "string",
                    "in:single_choice,multiple_choice,true_false",
                ],
                "questions.*.score" => ["required", "numeric", "min:0"],
                "questions.*.options" => ["required", "array", "min:2"],
                "questions.*.options.*.option_text" => ["required", "string"],
                "questions.*.options.*.is_correct" => ["required", "boolean"],
            ]);

            $quiz = $this->instructorCourseService->createInstructorQuiz(
                $request->user(),
                $data,
            );

            return ApiResponse::success(
                new InstructorQuizResource($quiz),
                "Thao tác thành công.",
                200,
            );
        }

        if (in_array($method, ["PUT", "PATCH"], true)) {
            if ($quizId === null) {
                throw new BusinessException("Thao tác thành công.", 422, [
                    "id" => ["Thao tác thành công."],
                ]);
            }

            $data = $this->validateInstructorQuizInput($request->all(), [
                "lesson_id" => ["sometimes", "integer", "exists:lessons,id"],
                "title" => ["sometimes", "string", "max:255"],
                "description" => ["nullable", "string"],
                "passing_score" => ["sometimes", "nullable", "numeric", "min:0"],
                "status" => ["sometimes", "string", "in:draft,published,hidden"],
                "questions" => ["sometimes", "array", "min:1"],
                "questions.*.question_text" => ["required", "string"],
                "questions.*.question_type" => [
                    "required",
                    "string",
                    "in:single_choice,multiple_choice,true_false",
                ],
                "questions.*.score" => ["required", "numeric", "min:0"],
                "questions.*.options" => ["required", "array", "min:2"],
                "questions.*.options.*.option_text" => ["required", "string"],
                "questions.*.options.*.is_correct" => ["required", "boolean"],
            ]);

            $quiz = $this->instructorCourseService->updateInstructorQuiz(
                $request->user(),
                $quizId,
                $data,
            );

            return ApiResponse::success(
                new InstructorQuizResource($quiz),
                "Thao tác thành công.",
                200,
            );
        }

        if ($method === "DELETE") {
            if ($quizId === null) {
                throw new BusinessException("Thao tác thành công.", 422, [
                    "id" => ["Thao tác thành công."],
                ]);
            }

            $this->instructorCourseService->deleteInstructorQuiz(
                $request->user(),
                $quizId,
            );

            return ApiResponse::success(
                null,
                "Thao tác thành công.",
                200,
            );
        }

        throw new BusinessException("Thao tác thành công.", 405);
    }

    private function validateInstructorQuizId(mixed $id): int
    {
        $validator = Validator::make(["id" => $id], [
            "id" => ["required", "integer", "min:1"],
        ]);

        if ($validator->fails()) {
            throw new BusinessException(
                "Thao tác thành công.",
                422,
                $validator->errors()->toArray(),
            );
        }

        return (int) $id;
    }

    private function validateInstructorQuizInput(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules, [
            "page.integer" => "Thao tác thành công.",
            "page.min" => "Thao tác thành công.",
            "per_page.integer" => "Thao tác thành công.",
            "per_page.min" => "Thao tác thành công.",
            "per_page.max" => "Thao tác thành công.",
            "course_id.integer" => "Thao tác thành công.",
            "course_id.exists" => "Thao tác thành công.",
            "lesson_id.required" => "Thao tác thành công.",
            "lesson_id.integer" => "Thao tác thành công.",
            "lesson_id.exists" => "Thao tác thành công.",
            "title.required" => "Thao tác thành công.",
            "title.string" => "Thao tác thành công.",
            "title.max" => "Thao tác thành công.",
            "description.string" => "Thao tác thành công.",
            "passing_score.numeric" => "Thao tác thành công.",
            "passing_score.min" => "Thao tác thành công.",
            "status.in" => "Thao tác thành công.",
            "questions.required" => "Thao tác thành công.",
            "questions.array" => "Thao tác thành công.",
            "questions.min" => "Thao tác thành công.",
            "questions.*.question_text.required" => "Thao tác thành công.",
            "questions.*.question_type.required" => "Thao tác thành công.",
            "questions.*.question_type.in" => "Thao tác thành công.",
            "questions.*.score.required" => "Thao tác thành công.",
            "questions.*.score.numeric" => "Thao tác thành công.",
            "questions.*.score.min" => "Thao tác thành công.",
            "questions.*.options.required" => "Thao tác thành công.",
            "questions.*.options.array" => "Thao tác thành công.",
            "questions.*.options.min" => "Thao tác thành công.",
            "questions.*.options.*.option_text.required" => "Thao tác thành công.",
            "questions.*.options.*.is_correct.required" => "Thao tác thành công.",
            "questions.*.options.*.is_correct.boolean" => "Thao tác thành công.",
        ]);

        if ($validator->fails()) {
            throw new BusinessException(
                "Thao tác thành công.",
                422,
                $validator->errors()->toArray(),
            );
        }

        return $validator->validated();
    }

    public function withdraw(InstructorWithdrawRequest $request): JsonResponse
    {
        $withdrawRequest = $this->instructorCourseService->createWithdrawRequest(
            $request->user(),
            $request->validated(),
        );

        return ApiResponse::success(
            new WithdrawRequestResource($withdrawRequest),
            "Thao tác thành công.",
            201,
        );
    }

    public function learners(CourseLearnerQueryRequest $request, int $id): JsonResponse
    {
        $paginator = $this->instructorCourseService->getCourseLearners(
            $id,
            $request->user()->id,
            $request->validated()
        );

        return ApiResponse::paginated(
            CourseLearnerResource::collection($paginator),
            $paginator,
            "Thao tác thành công.",
        );
    }
    public function checklist(\App\Http\Requests\Instructor\CourseChecklistRequest $request, mixed $courseId, \App\Services\Instructor\CourseChecklistService $service): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();

        $checklist = $service->getChecklist(
            (int) $request->user()->id,
            (int) $validated['course_id'],
            $validated
        );

        return \App\Support\ApiResponse::success(
            new \App\Http\Resources\Instructor\CourseChecklistResource($checklist),
            'Thao tác thành công.'
        );
    }

public function allLearners(InstructorLearnerIndexRequest $request): JsonResponse
    {
        $paginator = app(InstructorLearnerService::class)->paginateLearners(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            InstructorLearnerResource::collection(collect($paginator->items()))->resolve($request),
            'Thao tác thành công.',
            200,
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

public function withdrawSummary(InstructorWithdrawSummaryRequest $request): JsonResponse
    {
        $data = app(InstructorWithdrawalService::class)->getSummary(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            (new InstructorWithdrawSummaryResource($data))->resolve($request),
            'Thao tác thành công.'
        );
    }

public function withdrawals(InstructorWithdrawIndexRequest $request): JsonResponse
    {
        $paginator = app(InstructorWithdrawalService::class)->paginateWithdrawals(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            InstructorWithdrawResource::collection(collect($paginator->items()))->resolve($request),
            'Thao tác thành công.',
            200,
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

public function index(InstructorCourseIndexRequest $request): JsonResponse
    {
        $paginator = app(InstructorCourseService::class)->paginateCourses(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            InstructorCourseResource::collection(collect($paginator->items()))->resolve($request),
            'Thao tác thành công.',
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