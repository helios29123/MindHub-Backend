<?php
namespace App\Http\Controllers;
use App\Http\Requests\Report\CompletionRateQueryRequest;
use App\Http\Resources\Report\CompletionRateResource;
use App\Services\Report\InstructorReportService;
use App\Http\Requests\Report\TopCourseReportRequest;
use App\Http\Resources\Report\TopCourseReportResource;
use App\Http\Requests\Report\TopInstructorReportRequest;
use App\Http\Resources\Report\TopInstructorReportResource;
use App\Http\Requests\Report\InactiveLearnerReportRequest;
use App\Http\Resources\Report\InactiveLearnerReportResource;
use App\Http\Requests\Report\DashboardReportRequest;
use App\Http\Requests\Report\RevenueReportRequest;
use App\Http\Resources\Report\RevenueReportResource;
use App\Http\Requests\Report\CourseDashboardRequest;
use App\Services\Report\ReportService;
use App\Support\ApiResponse;

final class ReportController extends Controller
{
    public function __construct(
        private readonly InstructorReportService $reportService
    ) {}
    public function completionRate(CompletionRateQueryRequest $request)
    {
        $paginator = $this->reportService->getCompletionRate($request->validated(), $request->user());
        return response()->json([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'summary' => [
                    'total_courses' => $paginator->total(),
                ],
                'items' => CompletionRateResource::collection($paginator),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ]
            ]
        ]);
    }

    public function topCourses(TopCourseReportRequest $request, ReportService $adminReportService)
    {
        $result = $adminReportService->getTopCoursesReport($request->validated());
        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'summary' => $result['summary'],
                'items' => TopCourseReportResource::collection($paginator),
            ],
            message: 'Lấy báo cáo khóa học bán chạy thành công',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function topInstructors(TopInstructorReportRequest $request, ReportService $adminReportService)
    {
        $result = $adminReportService->getTopInstructorsReport($request->validated());
        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'items' => TopInstructorReportResource::collection($paginator),
            ],
            message: 'Lấy báo cáo giảng viên thành công',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function inactiveLearners(InactiveLearnerReportRequest $request, ReportService $adminReportService)
    {
        $validated = $request->validated();
        if (!empty($validated['course_id'])) {
            $course = \App\Models\Course::find($validated['course_id']);
            if ($course && $course->instructor_id !== $request->user()->id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        $result = $adminReportService->getInactiveLearnersReport($request->user()->id, $validated);
        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'items' => InactiveLearnerReportResource::collection($paginator),
            ],
            message: 'Lấy báo cáo học viên bỏ dở thành công',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function dashboard(DashboardReportRequest $request, ReportService $adminReportService)
    {
        $result = $adminReportService->getSystemDashboard($request->validated());

        return ApiResponse::success(
            data: $result,
            message: 'Lấy dashboard tổng quan thành công'
        );
    }

    public function revenueReport(RevenueReportRequest $request, ReportService $adminReportService)
    {
        $result = $adminReportService->getRevenueReport($request->validated());
        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'summary' => $result['summary'],
                'items' => RevenueReportResource::collection($paginator),
            ],
            message: 'Lấy báo cáo doanh thu thành công',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function courseDashboard(CourseDashboardRequest $request, int $id, ReportService $adminReportService)
    {
        $result = $adminReportService->getInstructorCourseDashboard($request->user()->id, $id, $request->validated());

        return ApiResponse::success(
            data: $result,
            message: 'Lấy dashboard khóa học thành công'
        );
    }

    /**
     * Get learner risk analytics for an instructor's course.
     *
     * @param \App\Http\Requests\Report\LearnerRiskQueryRequest $request
     * @param int $courseId
     * @param \App\Services\Report\LearnerRiskService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function learnerRisk(\App\Http\Requests\Report\LearnerRiskQueryRequest $request, mixed $courseId, \App\Services\Report\LearnerRiskService $service): \Illuminate\Http\JsonResponse
    {
        $instructorId = $request->user()->id;
        $filters = $request->validated();
        
        $paginator = $service->getLearnerRiskReport($instructorId, (int) $courseId, $filters);
        
        return ApiResponse::success(
            data: [
                'items' => \App\Http\Resources\Report\LearnerRiskResource::collection($paginator),
            ],
            message: 'Lấy phân tích học viên có nguy cơ bỏ học thành công',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    /**
     * Get specific analytics for an instructor's course.
     *
     * @param \App\Http\Requests\Report\CourseAnalyticsQueryRequest $request
     * @param mixed $courseId
     * @param \App\Services\Report\CourseAnalyticsService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseAnalytics(\App\Http\Requests\Report\CourseAnalyticsQueryRequest $request, mixed $courseId, \App\Services\Report\CourseAnalyticsService $service): \Illuminate\Http\JsonResponse
    {
        $instructorId = $request->user()->id;
        $filters = $request->validated();
        
        $result = $service->getCourseAnalytics($instructorId, (int) $courseId, $filters);
        
        return ApiResponse::success(
            data: new \App\Http\Resources\Report\CourseAnalyticsResource($result),
            message: 'Lấy phân tích khóa học thành công'
        );
    }
}