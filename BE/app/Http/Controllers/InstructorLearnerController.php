<?php
namespace App\Http\Controllers;
use App\Http\Requests\Instructor\EnrollmentShowRequest;
use App\Http\Requests\Instructor\LearnerQueryRequest;
use App\Http\Requests\Instructor\LearnerSummaryRequest;
use App\Http\Requests\Instructor\LessonProgressQueryRequest;
use App\Http\Resources\Instructor\EnrollmentDetailResource;
use App\Http\Resources\Instructor\LearnerResource;
use App\Http\Resources\Instructor\LearnerSummaryResource;
use App\Services\Instructor\InstructorLearnerService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
final class InstructorLearnerController extends Controller
{
    public function __construct(
        private readonly InstructorLearnerService $instructorLearnerService,
    ) {
    }
    public function summary(LearnerSummaryRequest $request): JsonResponse
    {
        $summary = $this->instructorLearnerService->getSummary(
            $request->user(),
            $request->validated(),
        );
        return ApiResponse::success(
            new LearnerSummaryResource($summary),
            'Lấy tổng quan học viên thành công.',
        );
    }
    public function index(LearnerQueryRequest $request): JsonResponse
    {
        $paginator = $this->instructorLearnerService->paginateLearners(
            $request->user(),
            $request->validated(),
        );
        return ApiResponse::paginated(
            LearnerResource::collection($paginator),
            $paginator,
            'Lấy danh sách học viên thành công.',
        );
    }
    public function courseOptions(Request $request): JsonResponse
    {
        $courses = $this->instructorLearnerService->getCourseOptions(
            $request->user(),
        );
        return ApiResponse::success(
            $courses,
            'Lấy danh sách khóa học cho bộ lọc thành công.',
        );
    }
    public function show(EnrollmentShowRequest $request, int $id): JsonResponse
    {
        $detail = $this->instructorLearnerService->getEnrollmentDetail(
            $request->user(),
            $id,
            $request->boolean('include_lesson_progress', true),
        );
        return ApiResponse::success(
            new EnrollmentDetailResource($detail),
            'Lấy chi tiết học viên thành công.',
        );
    }
    public function lessonProgress(
        LessonProgressQueryRequest $request,
        int $id,
    ): JsonResponse {
        $progress = $this->instructorLearnerService->getLessonProgress(
            $request->user(),
            $id,
            $request->boolean('group_by_section', true),
        );
        return ApiResponse::success(
            $progress,
            'Lấy tiến độ bài học thành công.',
        );
    }
}