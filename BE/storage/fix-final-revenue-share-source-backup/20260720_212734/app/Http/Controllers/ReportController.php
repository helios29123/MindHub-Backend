<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\CompletionRateQueryRequest;
use App\Http\Requests\Report\CourseAnalyticsQueryRequest;
use App\Http\Requests\Report\CourseDashboardRequest;
use App\Http\Requests\Report\DashboardReportRequest;
use App\Http\Requests\Report\InactiveLearnerReportRequest;
use App\Http\Requests\Report\InstructorDashboardAlertQueryRequest;
use App\Http\Requests\Report\InstructorDashboardQueryRequest;
use App\Http\Requests\Report\InstructorEnrollmentChartQueryRequest;
use App\Http\Requests\Report\InstructorRevenueChartQueryRequest;
use App\Http\Requests\Report\InstructorTopCourseQueryRequest;
use App\Http\Requests\Report\LearnerRiskQueryRequest;
use App\Http\Requests\Report\RevenueReportRequest;
use App\Http\Requests\Report\TopCourseReportRequest;
use App\Http\Requests\Report\TopInstructorReportRequest;
use App\Http\Resources\Report\CompletionRateResource;
use App\Http\Resources\Report\CourseAnalyticsResource;
use App\Http\Resources\Report\InactiveLearnerReportResource;
use App\Http\Resources\Report\InstructorDashboardAlertResource;
use App\Http\Resources\Report\InstructorDashboardResource;
use App\Http\Resources\Report\InstructorEnrollmentChartResource;
use App\Http\Resources\Report\InstructorRevenueChartResource;
use App\Http\Resources\Report\InstructorTopCourseResource;
use App\Http\Resources\Report\LearnerRiskResource;
use App\Http\Resources\Report\RevenueReportResource;
use App\Http\Resources\Report\TopCourseReportResource;
use App\Http\Resources\Report\TopInstructorReportResource;
use App\Models\Course;
use App\Services\Report\CourseAnalyticsService;
use App\Services\Report\InstructorDashboardAlertService;
use App\Services\Report\InstructorDashboardService;
use App\Services\Report\InstructorEnrollmentChartService;
use App\Services\Report\InstructorReportService;
use App\Services\Report\InstructorRevenueChartService;
use App\Services\Report\InstructorTopCourseService;
use App\Services\Report\LearnerRiskService;
use App\Services\Report\ReportService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ReportController extends Controller
{
    public function __construct(
        private readonly InstructorReportService $reportService
    ) {
    }

    public function completionRate(CompletionRateQueryRequest $request): JsonResponse
    {
        $paginator = $this->reportService->getCompletionRate(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Lấy báo cáo tỷ lệ hoàn thành thành công.',
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
                ],
            ],
        ]);
    }

    public function topCourses(
        TopCourseReportRequest $request,
        ReportService $adminReportService
    ): JsonResponse {
        $result = $adminReportService->getTopCoursesReport($request->validated());
        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'summary' => $result['summary'],
                'items' => TopCourseReportResource::collection($paginator),
            ],
            message: 'Lấy báo cáo top khóa học thành công.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function topInstructors(
        TopInstructorReportRequest $request,
        ReportService $adminReportService
    ): JsonResponse {
        $result = $adminReportService->getTopInstructorsReport($request->validated());
        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'items' => TopInstructorReportResource::collection($paginator),
            ],
            message: 'Lấy báo cáo top giảng viên thành công.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function inactiveLearners(
        InactiveLearnerReportRequest $request,
        ReportService $adminReportService
    ): JsonResponse {
        $validated = $request->validated();

        if (!empty($validated['course_id'])) {
            $course = Course::find($validated['course_id']);

            if ($course && $course->instructor_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xem dữ liệu khóa học này.',
                ], 403);
            }
        }

        $result = $adminReportService->getInactiveLearnersReport(
            $request->user()->id,
            $validated
        );

        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'items' => InactiveLearnerReportResource::collection($paginator),
            ],
            message: 'Lấy danh sách học viên không hoạt động thành công.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function dashboard(
        DashboardReportRequest $request,
        ReportService $adminReportService
    ): JsonResponse {
        $result = $adminReportService->getSystemDashboard($request->validated());

        return ApiResponse::success(
            data: $result,
            message: 'Lấy dashboard hệ thống thành công.'
        );
    }

    public function revenueReport(
        RevenueReportRequest $request,
        ReportService $adminReportService
    ): JsonResponse {
        $result = $adminReportService->getRevenueReport($request->validated());
        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'summary' => $result['summary'],
                'items' => RevenueReportResource::collection($paginator),
            ],
            message: 'Lấy báo cáo doanh thu thành công.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function courseDashboard(
        CourseDashboardRequest $request,
        int $id,
        ReportService $adminReportService
    ): JsonResponse {
        $result = $adminReportService->getInstructorCourseDashboard(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return ApiResponse::success(
            data: $result,
            message: 'Lấy dashboard khóa học thành công.'
        );
    }

    public function learnerRisk(
        LearnerRiskQueryRequest $request,
        mixed $courseId,
        LearnerRiskService $service
    ): JsonResponse {
        $instructorId = $request->user()->id;
        $filters = $request->validated();

        $paginator = $service->getLearnerRiskReport(
            $instructorId,
            (int) $courseId,
            $filters
        );

        return ApiResponse::success(
            data: [
                'items' => LearnerRiskResource::collection($paginator),
            ],
            message: 'Lấy báo cáo nguy cơ bỏ học thành công.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function courseAnalytics(
        CourseAnalyticsQueryRequest $request,
        mixed $courseId,
        CourseAnalyticsService $service
    ): JsonResponse {
        $instructorId = $request->user()->id;
        $filters = $request->validated();

        $result = $service->getCourseAnalytics(
            $instructorId,
            (int) $courseId,
            $filters
        );

        return ApiResponse::success(
            data: new CourseAnalyticsResource($result),
            message: 'Lấy thống kê khóa học thành công.'
        );
    }

    public function instructorDashboard(
        InstructorDashboardQueryRequest $request
    ): JsonResponse {
        $data = app(InstructorDashboardService::class)->getDashboard(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            (new InstructorDashboardResource($data))->resolve($request),
            'Lấy dashboard giảng viên thành công.'
        );
    }

    public function instructorRevenueChart(
        InstructorRevenueChartQueryRequest $request
    ): JsonResponse {
        $data = app(InstructorRevenueChartService::class)->getChart(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            InstructorRevenueChartResource::collection(collect($data))->resolve($request),
            'Lấy biểu đồ doanh thu thành công.'
        );
    }

    public function instructorEnrollmentChart(
        InstructorEnrollmentChartQueryRequest $request
    ): JsonResponse {
        $data = app(InstructorEnrollmentChartService::class)->getChart(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            InstructorEnrollmentChartResource::collection(collect($data))->resolve($request),
            'Lấy biểu đồ ghi danh thành công.'
        );
    }

    public function instructorTopCourses(
        InstructorTopCourseQueryRequest $request
    ): JsonResponse {
        $data = app(InstructorTopCourseService::class)->getTopCourses(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            InstructorTopCourseResource::collection(collect($data))->resolve($request),
            'Lấy top khóa học thành công.'
        );
    }

    public function instructorDashboardAlerts(
        InstructorDashboardAlertQueryRequest $request
    ): JsonResponse {
        $data = app(InstructorDashboardAlertService::class)->getAlerts(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            InstructorDashboardAlertResource::collection(collect($data))->resolve($request),
            'Lấy thông báo dashboard thành công.'
        );
    }

    public function incompleteCourses(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $courses = \Illuminate\Support\Facades\DB::table('courses')
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->get();

        $incomplete = [];
        $checklistService = app(CourseChecklistService::class);

        foreach ($courses as $course) {
            try {
                $checklist = $checklistService->getChecklist($instructorId, $course->id);
                if (!$checklist['passed']) {
                    $incomplete[] = [
                        'id' => (int) $course->id,
                        'title' => $course->title,
                        'status' => $course->status,
                        'missing_items' => $checklist['missing_items'],
                        'warnings' => $checklist['warnings'],
                    ];
                }
            } catch (\Exception $e) {
                // Skip if error
            }
        }

        return ApiResponse::success(
            $incomplete,
            'Lấy danh sách khóa học chưa hoàn thiện thành công.'
        );
    }

    public function courseBreakdown(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;

        $breakdown = \Illuminate\Support\Facades\DB::table('courses')
            ->leftJoin('revenues', function ($join) {
                $join->on('revenues.course_id', '=', 'courses.id')
                    ->whereIn('revenues.status', ['available', 'withdrawn']);
            })
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->selectRaw('
                courses.id as course_id,
                courses.title,
                courses.status,
                COUNT(revenues.id) as total_orders,
                COALESCE(SUM(revenues.gross_amount), 0) as gross_amount,
                COALESCE(SUM(revenues.instructor_amount), 0) as instructor_amount,
                COALESCE(SUM(revenues.platform_fee_amount), 0) as platform_fee_amount
            ')
            ->groupBy('courses.id', 'courses.title', 'courses.status')
            ->get()
            ->map(function ($row) {
                return [
                    'course_id' => (int) $row->course_id,
                    'title' => $row->title,
                    'status' => $row->status,
                    'total_orders' => (int) $row->total_orders,
                    'gross_amount' => number_format((float) $row->gross_amount, 2, '.', ''),
                    'instructor_amount' => number_format((float) $row->instructor_amount, 2, '.', ''),
                    'platform_fee_amount' => number_format((float) $row->platform_fee_amount, 2, '.', ''),
                ];
            })
            ->all();

        return ApiResponse::success(
            $breakdown,
            'Lấy báo cáo doanh thu theo khóa học thành công.'
        );
    }

    public function topCoursesByRevenue(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $limit = min(max((int) ($request->query('limit') ?? 10), 1), 20);

        $top = \Illuminate\Support\Facades\DB::table('courses')
            ->join('revenues', function ($join) {
                $join->on('revenues.course_id', '=', 'courses.id')
                    ->whereIn('revenues.status', ['available', 'withdrawn']);
            })
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->selectRaw('
                courses.id as course_id,
                courses.title,
                courses.status,
                COUNT(revenues.id) as total_orders,
                COALESCE(SUM(revenues.gross_amount), 0) as gross_amount,
                COALESCE(SUM(revenues.instructor_amount), 0) as instructor_amount,
                COALESCE(SUM(revenues.platform_fee_amount), 0) as platform_fee_amount
            ')
            ->groupBy('courses.id', 'courses.title', 'courses.status')
            ->orderByDesc('instructor_amount')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'course_id' => (int) $row->course_id,
                    'title' => $row->title,
                    'status' => $row->status,
                    'total_orders' => (int) $row->total_orders,
                    'gross_amount' => number_format((float) $row->gross_amount, 2, '.', ''),
                    'instructor_amount' => number_format((float) $row->instructor_amount, 2, '.', ''),
                    'platform_fee_amount' => number_format((float) $row->platform_fee_amount, 2, '.', ''),
                ];
            })
            ->all();

        return ApiResponse::success(
            $top,
            'Lấy top khóa học theo doanh thu thành công.'
        );
    }

    public function revenueSummary(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;

        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        if ($request->query('date_from') && $request->query('date_to')) {
            $startDate = \Illuminate\Support\Carbon::parse($request->query('date_from'));
            $endDate = \Illuminate\Support\Carbon::parse($request->query('date_to'));
        }

        $row = \Illuminate\Support\Facades\DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->whereBetween('earned_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereIn('status', ['available', 'withdrawn'])
            ->selectRaw('
                COALESCE(SUM(gross_amount), 0) as gross_amount,
                COALESCE(SUM(instructor_amount), 0) as instructor_amount,
                COALESCE(SUM(platform_fee_amount), 0) as platform_fee_amount
            ')
            ->first();

        return ApiResponse::success([
            'gross_amount' => number_format((float) ($row->gross_amount ?? 0), 2, '.', ''),
            'instructor_amount' => number_format((float) ($row->instructor_amount ?? 0), 2, '.', ''),
            'platform_fee_amount' => number_format((float) ($row->platform_fee_amount ?? 0), 2, '.', ''),
        ], 'Lấy tổng quan doanh thu thành công.');
    }
}
