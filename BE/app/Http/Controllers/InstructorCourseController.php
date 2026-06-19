<?php

namespace App\Http\Controllers;

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
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
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
            "T蘯｡o khﾃｳa h盻皇 thﾃnh cﾃｴng.",
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
            "L蘯･y danh sﾃ｡ch bﾃi h盻皇 thﾃnh cﾃｴng.",
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
            "T蘯｡o bﾃi h盻皇 thﾃnh cﾃｴng.",
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
            "L蘯･y chi ti蘯ｿt bﾃi h盻皇 thﾃnh cﾃｴng.",
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
            "C蘯ｭp nh蘯ｭt bﾃi h盻皇 thﾃnh cﾃｴng.",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
        );
    }

    public function destroyLesson(int $id): JsonResponse
    {
        $this->instructorCourseService->deleteLesson(request()->user(), $id);

        return ApiResponse::success(
            [],
            "Xﾃｳa bﾃi h盻皇 thﾃnh cﾃｴng.",
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
            "Upload video bﾃi h盻皇 thﾃnh cﾃｴng.",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
            201,
        );
    }

    public function reviewNotes(string $id): JsonResponse
    {
        if (!ctype_digit($id) || (int) $id < 1) {
            throw new BusinessException(
                "Tham s盻・khﾃｴng h盻｣p l盻・",
                422,
            );
        }

        $course = $this->instructorCourseService->getRejectedReviewNotes(
            request()->user(),
            (int) $id,
        );

        return ApiResponse::success(
            new ReviewNoteResource($course),
            "L蘯･y d盻ｯ li盻㎡ thﾃnh cﾃｴng.",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "message" => "Thao tﾃ｡c thﾃnh cﾃｴng",
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
                    "Thao tﾃ｡c thﾃnh cﾃｴng",
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
                "Thao tﾃ｡c thﾃnh cﾃｴng",
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
                "Thao tﾃ｡c thﾃnh cﾃｴng",
                200,
            );
        }

        if (in_array($method, ["PUT", "PATCH"], true)) {
            if ($quizId === null) {
                throw new BusinessException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・", 422, [
                    "id" => ["Quiz id lﾃ b蘯ｯt bu盻冂."],
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
                "Thao tﾃ｡c thﾃnh cﾃｴng",
                200,
            );
        }

        if ($method === "DELETE") {
            if ($quizId === null) {
                throw new BusinessException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・", 422, [
                    "id" => ["Quiz id lﾃ b蘯ｯt bu盻冂."],
                ]);
            }

            $this->instructorCourseService->deleteInstructorQuiz(
                $request->user(),
                $quizId,
            );

            return ApiResponse::success(
                null,
                "Thao tﾃ｡c thﾃnh cﾃｴng",
                200,
            );
        }

        throw new BusinessException("Phﾆｰﾆ｡ng th盻ｩc khﾃｴng ﾄ柁ｰ盻｣c h盻・tr盻｣.", 405);
    }

    private function validateInstructorQuizId(mixed $id): int
    {
        $validator = Validator::make(["id" => $id], [
            "id" => ["required", "integer", "min:1"],
        ]);

        if ($validator->fails()) {
            throw new BusinessException(
                "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・",
                422,
                $validator->errors()->toArray(),
            );
        }

        return (int) $id;
    }

    private function validateInstructorQuizInput(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules, [
            "page.integer" => "Trang ph蘯｣i lﾃ s盻・nguyﾃｪn.",
            "page.min" => "Trang ph蘯｣i l盻嬾 hﾆ｡n ho蘯ｷc b蘯ｱng 1.",
            "per_page.integer" => "S盻・dﾃｲng m盻擁 trang ph蘯｣i lﾃ s盻・nguyﾃｪn.",
            "per_page.min" => "S盻・dﾃｲng m盻擁 trang ph蘯｣i l盻嬾 hﾆ｡n ho蘯ｷc b蘯ｱng 1.",
            "per_page.max" => "S盻・dﾃｲng m盻擁 trang khﾃｴng ﾄ柁ｰ盻｣c vﾆｰ盻｣t quﾃ｡ 100.",
            "course_id.integer" => "Khﾃｳa h盻皇 khﾃｴng h盻｣p l盻・",
            "course_id.exists" => "Khﾃｳa h盻皇 khﾃｴng t盻渡 t蘯｡i.",
            "lesson_id.required" => "Bﾃi h盻皇 lﾃ b蘯ｯt bu盻冂.",
            "lesson_id.integer" => "Bﾃi h盻皇 khﾃｴng h盻｣p l盻・",
            "lesson_id.exists" => "Bﾃi h盻皇 khﾃｴng t盻渡 t蘯｡i.",
            "title.required" => "Tiﾃｪu ﾄ黛ｻ・quiz lﾃ b蘯ｯt bu盻冂.",
            "title.string" => "Tiﾃｪu ﾄ黛ｻ・quiz ph蘯｣i lﾃ chu盻擁.",
            "title.max" => "Tiﾃｪu ﾄ黛ｻ・quiz khﾃｴng ﾄ柁ｰ盻｣c vﾆｰ盻｣t quﾃ｡ 255 kﾃｽ t盻ｱ.",
            "description.string" => "Mﾃｴ t蘯｣ ph蘯｣i lﾃ chu盻擁.",
            "passing_score.numeric" => "ﾄ進盻ノ ﾄ黛ｺ｡t ph蘯｣i lﾃ s盻・",
            "passing_score.min" => "ﾄ進盻ノ ﾄ黛ｺ｡t ph蘯｣i l盻嬾 hﾆ｡n ho蘯ｷc b蘯ｱng 0.",
            "status.in" => "Tr蘯｡ng thﾃ｡i quiz khﾃｴng h盻｣p l盻・",
            "questions.required" => "Danh sﾃ｡ch cﾃ｢u h盻淑 lﾃ b蘯ｯt bu盻冂.",
            "questions.array" => "Danh sﾃ｡ch cﾃ｢u h盻淑 khﾃｴng h盻｣p l盻・",
            "questions.min" => "Quiz ph蘯｣i cﾃｳ ﾃｭt nh蘯･t m盻冲 cﾃ｢u h盻淑.",
            "questions.*.question_text.required" => "N盻冓 dung cﾃ｢u h盻淑 lﾃ b蘯ｯt bu盻冂.",
            "questions.*.question_type.required" => "Lo蘯｡i cﾃ｢u h盻淑 lﾃ b蘯ｯt bu盻冂.",
            "questions.*.question_type.in" => "Lo蘯｡i cﾃ｢u h盻淑 khﾃｴng h盻｣p l盻・",
            "questions.*.score.required" => "ﾄ進盻ノ cﾃ｢u h盻淑 lﾃ b蘯ｯt bu盻冂.",
            "questions.*.score.numeric" => "ﾄ進盻ノ cﾃ｢u h盻淑 ph蘯｣i lﾃ s盻・",
            "questions.*.score.min" => "ﾄ進盻ノ cﾃ｢u h盻淑 ph蘯｣i l盻嬾 hﾆ｡n ho蘯ｷc b蘯ｱng 0.",
            "questions.*.options.required" => "Danh sﾃ｡ch ﾄ妥｡p ﾃ｡n lﾃ b蘯ｯt bu盻冂.",
            "questions.*.options.array" => "Danh sﾃ｡ch ﾄ妥｡p ﾃ｡n khﾃｴng h盻｣p l盻・",
            "questions.*.options.min" => "M盻擁 cﾃ｢u h盻淑 ph蘯｣i cﾃｳ ﾃｭt nh蘯･t 2 ﾄ妥｡p ﾃ｡n.",
            "questions.*.options.*.option_text.required" => "N盻冓 dung ﾄ妥｡p ﾃ｡n lﾃ b蘯ｯt bu盻冂.",
            "questions.*.options.*.is_correct.required" => "Tr蘯｡ng thﾃ｡i ﾄ妥｡p ﾃ｡n ﾄ妥ｺng/sai lﾃ b蘯ｯt bu盻冂.",
            "questions.*.options.*.is_correct.boolean" => "Tr蘯｡ng thﾃ｡i ﾄ妥｡p ﾃ｡n ﾄ妥ｺng/sai ph蘯｣i lﾃ boolean.",
        ]);

        if ($validator->fails()) {
            throw new BusinessException(
                "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            "Thao tﾃ｡c thﾃnh cﾃｴng",
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
            'Lấy checklist khóa học thành công.'
        );
    }
}
