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
use Illuminate\Support\Facades\Storage;
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

    public function toggleFeatured(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'is_featured' => ['required', 'boolean'],
        ]);

        $course = $this->instructorCourseService->toggleFeatured(
            $request->user(),
            $id,
            (bool) $request->input('is_featured')
        );

        return ApiResponse::success(
            new InstructorCourseResource($course),
            $course->is_featured ? 'Bật khóa học nổi bật thành công.' : 'Tắt khóa học nổi bật thành công.'
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
            200,
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

    public function learnersSummary(Request $request): JsonResponse
    {
        try {
            $summary = app(InstructorLearnerService::class)->getLearnersSummary(
                (int) $request->user()->id,
                $request->all()
            );

            return ApiResponse::success($summary, 'Lấy thống kê học viên thành công.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function learnersChart(Request $request): JsonResponse
    {
        try {
            $chart = app(InstructorLearnerService::class)->getLearnersChart(
                (int) $request->user()->id,
                $request->all()
            );

            return ApiResponse::success($chart, 'Lấy biểu đồ học viên thành công.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function showLearnerDetails(Request $request, mixed $id): JsonResponse
    {
        try {
            $details = app(InstructorLearnerService::class)->getLearnerDetails(
                (int) $request->user()->id,
                (int) $id
            );

            return ApiResponse::success($details, 'Lấy chi tiết học viên thành công.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }

    public function exportLearners(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
    {
        try {
            $filters = $request->all();
            $records = app(InstructorLearnerService::class)->exportLearners(
                (int) $request->user()->id,
                $filters
            );

            $repo = app(\App\Repositories\Instructor\InstructorLearnerRepository::class);
            $period = $repo->resolvePeriod($filters);
            $dateFromStr = $period['current_from']->format('Y-m-d');
            $dateToStr = $period['current_to']->format('Y-m-d');
            $filename = 'hoc-vien-' . $dateFromStr . '-den-' . $dateToStr . '.csv';

            return response()->stream(function () use ($records) {
                $handle = fopen('php://output', 'w');
                // Output UTF-8 BOM for Excel compatibility
                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, ['Mã Ghi Danh', 'ID Học Viên', 'Họ Và Tên', 'Email', 'ID Khóa Học', 'Tên Khóa Học', 'Trạng Thái', 'Tiến Độ (%)', 'Ngày Ghi Danh', 'Lần Học Gần Nhất']);

                foreach ($records as $row) {
                    fputcsv($handle, [
                        $row->enrollment_id,
                        $row->learner_id,
                        $row->learner_name,
                        $row->learner_email,
                        $row->course_id,
                        $row->course_title,
                        $row->status === 'completed' || $row->progress_percent >= 100 ? 'Đã hoàn thành' : 'Đang học',
                        $row->progress_percent . '%',
                        $row->enrolled_at,
                        $row->last_accessed_at ?? $row->enrolled_at
                    ]);
                }

                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            return ApiResponse::error($exception->getMessage(), [], $exception->getStatusCode());
        }
    }


    public function summary(Request $request): JsonResponse
    {
        $dashboardData = app(\App\Repositories\Report\InstructorDashboardRepository::class)->getDashboard((int) $request->user()->id, []);
        return ApiResponse::success($dashboardData['course_summary'] ?? [], 'Lấy thống kê khóa học thành công.');
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


    public function hide(Request $request, int $id): JsonResponse
    {
        $course = $this->instructorCourseService->hideCourse($request->user(), $id);

        return ApiResponse::success(
            new InstructorCourseResource($course),
            'Đã ẩn khóa học.'
        );
    }

    public function unhide(Request $request, int $id): JsonResponse
    {
        $course = $this->instructorCourseService->unhideCourse($request->user(), $id);

        return ApiResponse::success(
            new InstructorCourseResource($course),
            'Đã hiện lại khóa học.'
        );
    }

    public function uploadMedia(Request $request): JsonResponse
    {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'csv', 'zip', 'rar', '7z', 'jpg', 'jpeg', 'png', 'webp', 'mp4', 'mov', 'webm'];
        $request->validate([
            'file' => [
                'required', 
                'file', 
                'max:204800',
                function (string $attribute, mixed $value, \Closure $fail) use ($allowedExtensions): void {
                    if (!$value instanceof \Illuminate\Http\UploadedFile) return;
                    $ext = strtolower((string) $value->getClientOriginalExtension());
                    if (!in_array($ext, $allowedExtensions, true)) {
                        $fail('Định dạng tập tin không được hỗ trợ.');
                    }
                }
            ],
            'type' => ['nullable', 'string'],
        ], [
            'file.required' => 'Vui lòng chọn tập tin để tải lên.',
            'file.file' => 'Tập tin không hợp lệ.',
            'file.max' => 'Dung lượng tập tin không được vượt quá 200MB.',
        ]);

        $file = $request->file('file');
        $type = $request->input('type', 'course_media');

        $path = $file->store('instructor/uploads/' . $type, 'public');
        $url = Storage::disk('public')->url($path);

        return ApiResponse::success([
            'url' => $url,
            'path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ], 'Tải lên tập tin thành công.', 201);
    }
}