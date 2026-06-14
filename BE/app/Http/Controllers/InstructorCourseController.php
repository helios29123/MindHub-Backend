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
use App\Http\Resources\Instructor\InstructorCourseResource;
use App\Http\Resources\Instructor\InstructorQuizResource;
use App\Http\Resources\Instructor\InstructorRevenueResource;
use App\Http\Resources\Instructor\InstructorSectionResource;
use App\Http\Resources\Instructor\LessonAssetResource;
use App\Http\Resources\Instructor\LessonResource;
use App\Http\Resources\Instructor\ReviewNoteResource;
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
            "Tạo khóa học thành công.",
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
            "Lấy danh sách bài học thành công.",
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
            "Tạo bài học thành công.",
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
            "Lấy chi tiết bài học thành công.",
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
            "Cập nhật bài học thành công.",
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
            "Thao tác thành công",
        );
    }

    public function destroyLesson(int $id): JsonResponse
    {
        $this->instructorCourseService->deleteLesson(request()->user(), $id);

        return ApiResponse::success(
            [],
            "Xóa bài học thành công.",
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
            "Upload video bài học thành công.",
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
            "Thao tác thành công",
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
            "Thao tác thành công",
            201,
        );
    }

    public function reviewNotes(string $id): JsonResponse
    {
        if (!ctype_digit($id) || (int) $id < 1) {
            throw new BusinessException(
                "Tham số không hợp lệ.",
                422,
            );
        }

        $course = $this->instructorCourseService->getRejectedReviewNotes(
            request()->user(),
            (int) $id,
        );

        return ApiResponse::success(
            new ReviewNoteResource($course),
            "Lấy dữ liệu thành công.",
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
            "Thao tác thành công",
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
            "Thao tác thành công",
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
            "Thao tác thành công",
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
            "Thao tác thành công",
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
            "Thao tác thành công",
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
            "Thao tác thành công",
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
            "Thao tác thành công",
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
            "Thao tác thành công",
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
            "message" => "Thao tác thành công",
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
                    "Thao tác thành công",
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
                "Thao tác thành công",
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
                "Thao tác thành công",
                200,
            );
        }

        if (in_array($method, ["PUT", "PATCH"], true)) {
            if ($quizId === null) {
                throw new BusinessException("Dữ liệu không hợp lệ.", 422, [
                    "id" => ["Quiz id là bắt buộc."],
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
                "Thao tác thành công",
                200,
            );
        }

        if ($method === "DELETE") {
            if ($quizId === null) {
                throw new BusinessException("Dữ liệu không hợp lệ.", 422, [
                    "id" => ["Quiz id là bắt buộc."],
                ]);
            }

            $this->instructorCourseService->deleteInstructorQuiz(
                $request->user(),
                $quizId,
            );

            return ApiResponse::success(
                null,
                "Thao tác thành công",
                200,
            );
        }

        throw new BusinessException("Phương thức không được hỗ trợ.", 405);
    }

    private function validateInstructorQuizId(mixed $id): int
    {
        $validator = Validator::make(["id" => $id], [
            "id" => ["required", "integer", "min:1"],
        ]);

        if ($validator->fails()) {
            throw new BusinessException(
                "Dữ liệu không hợp lệ.",
                422,
                $validator->errors()->toArray(),
            );
        }

        return (int) $id;
    }

    private function validateInstructorQuizInput(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules, [
            "page.integer" => "Trang phải là số nguyên.",
            "page.min" => "Trang phải lớn hơn hoặc bằng 1.",
            "per_page.integer" => "Số dòng mỗi trang phải là số nguyên.",
            "per_page.min" => "Số dòng mỗi trang phải lớn hơn hoặc bằng 1.",
            "per_page.max" => "Số dòng mỗi trang không được vượt quá 100.",
            "course_id.integer" => "Khóa học không hợp lệ.",
            "course_id.exists" => "Khóa học không tồn tại.",
            "lesson_id.required" => "Bài học là bắt buộc.",
            "lesson_id.integer" => "Bài học không hợp lệ.",
            "lesson_id.exists" => "Bài học không tồn tại.",
            "title.required" => "Tiêu đề quiz là bắt buộc.",
            "title.string" => "Tiêu đề quiz phải là chuỗi.",
            "title.max" => "Tiêu đề quiz không được vượt quá 255 ký tự.",
            "description.string" => "Mô tả phải là chuỗi.",
            "passing_score.numeric" => "Điểm đạt phải là số.",
            "passing_score.min" => "Điểm đạt phải lớn hơn hoặc bằng 0.",
            "status.in" => "Trạng thái quiz không hợp lệ.",
            "questions.required" => "Danh sách câu hỏi là bắt buộc.",
            "questions.array" => "Danh sách câu hỏi không hợp lệ.",
            "questions.min" => "Quiz phải có ít nhất một câu hỏi.",
            "questions.*.question_text.required" => "Nội dung câu hỏi là bắt buộc.",
            "questions.*.question_type.required" => "Loại câu hỏi là bắt buộc.",
            "questions.*.question_type.in" => "Loại câu hỏi không hợp lệ.",
            "questions.*.score.required" => "Điểm câu hỏi là bắt buộc.",
            "questions.*.score.numeric" => "Điểm câu hỏi phải là số.",
            "questions.*.score.min" => "Điểm câu hỏi phải lớn hơn hoặc bằng 0.",
            "questions.*.options.required" => "Danh sách đáp án là bắt buộc.",
            "questions.*.options.array" => "Danh sách đáp án không hợp lệ.",
            "questions.*.options.min" => "Mỗi câu hỏi phải có ít nhất 2 đáp án.",
            "questions.*.options.*.option_text.required" => "Nội dung đáp án là bắt buộc.",
            "questions.*.options.*.is_correct.required" => "Trạng thái đáp án đúng/sai là bắt buộc.",
            "questions.*.options.*.is_correct.boolean" => "Trạng thái đáp án đúng/sai phải là boolean.",
        ]);

        if ($validator->fails()) {
            throw new BusinessException(
                "Dữ liệu không hợp lệ.",
                422,
                $validator->errors()->toArray(),
            );
        }

        return $validator->validated();
    }
}
