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
use Illuminate\Http\Request;
use App\Services\Instructor\CourseChecklistService;

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
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Chưa đăng nhập.', 401);
        }
        $instructorId = (int) $user->id;
        $limit = min(max((int) ($request->query('limit') ?? $request->query('per_page') ?? 5), 1), 20);

        $courses = \Illuminate\Support\Facades\DB::table('courses')
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['published', 'approved', 'active', 'pending_review', 'pending'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $incomplete = [];
        $checklistService = app(CourseChecklistService::class);

        foreach ($courses as $course) {
            try {
                $completion = $checklistService->calculateCompletion($instructorId, $course);

                $incomplete[] = [
                    'id' => (int) $course->id,
                    'title' => $course->title,
                    'status' => $course->status,
                    'completion_percentage' => $completion['completion_percent'],
                    'completion_percent' => $completion['completion_percent'],
                    'completed_items' => $completion['completed_items'],
                    'total_items' => $completion['total_items'],
                    'missing_items' => $completion['missing_items'],
                    'next_step' => $completion['next_step'],
                    'action_label' => $completion['action_label'],
                    'updated_at' => $course->updated_at,
                ];

                if (count($incomplete) >= $limit) {
                    break;
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
        $period = $this->resolvePeriod($request->all());

        $query = \Illuminate\Support\Facades\DB::table('courses')
            ->leftJoin('revenues', function ($join) use ($period) {
                $join->on('revenues.course_id', '=', 'courses.id')
                    ->whereIn('revenues.status', ['pending', 'available', 'scheduled', 'included_in_payout', 'paid', 'withdrawn'])
                    ->whereBetween('revenues.earned_at', [$period['current_from'], $period['current_to']]);
            })
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at');

        if ($request->query('course_id') && $request->query('course_id') !== 'all') {
            $query->where('courses.id', (int) $request->query('course_id'));
        }

        $breakdown = $query->selectRaw('
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
        $period = $this->resolvePeriod($request->all());
        $limit = min(max((int) ($request->query('limit') ?? 10), 1), 20);

        $coursesQuery = \Illuminate\Support\Facades\DB::table('courses')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at');

        if ($request->query('course_id') && $request->query('course_id') !== 'all') {
            $coursesQuery->where('courses.id', (int) $request->query('course_id'));
        }

        $courses = $coursesQuery->get(['id', 'title', 'status', 'thumbnail_url', 'level', 'price']);
        $courseIds = $courses->pluck('id')->toArray();

        if (empty($courseIds)) {
            return ApiResponse::success([], 'Lấy top khóa học theo doanh thu thành công.');
        }

        $enrollmentsMap = \Illuminate\Support\Facades\DB::table('enrollments')
            ->whereIn('course_id', $courseIds)
            ->whereIn('status', ['active', 'completed'])
            ->select('course_id', \Illuminate\Support\Facades\DB::raw('COUNT(id) as enrollment_count'), \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT user_id) as unique_learner_count'))
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $revenuesQuery = \Illuminate\Support\Facades\DB::table('revenues')
            ->whereIn('course_id', $courseIds)
            ->whereIn('status', ['pending', 'available', 'scheduled', 'included_in_payout', 'paid', 'withdrawn']);

        if ($period['preset'] !== 'all' && $period['preset'] !== 'all_time') {
            $revenuesQuery->whereBetween('earned_at', [$period['current_from'], $period['current_to']]);
        }

        $revenuesMap = $revenuesQuery
            ->select('course_id', \Illuminate\Support\Facades\DB::raw('COUNT(id) as total_orders'), \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(instructor_amount), 0) as total_instructor_revenue'), \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(gross_amount), 0) as total_gross_revenue'), \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(platform_fee_amount), 0) as total_platform_fee'))
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $items = [];
        foreach ($courses as $c) {
            $e = $enrollmentsMap->get($c->id);
            $r = $revenuesMap->get($c->id);

            $eCount = $e ? (int) $e->enrollment_count : 0;
            $uCount = $e ? (int) $e->unique_learner_count : 0;
            $orders = $r ? (int) $r->total_orders : 0;
            $instRev = $r ? (float) $r->total_instructor_revenue : 0.0;
            $grossRev = $r ? (float) $r->total_gross_revenue : 0.0;
            $feeRev = $r ? (float) $r->total_platform_fee : 0.0;

            $items[] = [
                'id' => (int) $c->id,
                'course_id' => (int) $c->id,
                'title' => $c->title,
                'status' => $c->status,
                'total_orders' => $orders,
                'enrollment_count' => $eCount,
                'enrollments_count' => $eCount,
                'studentCount' => $uCount,
                'student_count' => $uCount,
                'learners_count' => $uCount,
                'unique_learner_count' => $uCount,
                'revenue' => $instRev,
                'instructor_revenue' => $instRev,
                'instructor_amount' => number_format($instRev, 2, '.', ''),
                'gross_amount' => number_format($grossRev, 2, '.', ''),
                'gross_revenue' => $grossRev,
                'platform_fee_amount' => number_format($feeRev, 2, '.', ''),
            ];
        }

        usort($items, function ($a, $b) {
            if ($b['revenue'] !== $a['revenue']) {
                return $b['revenue'] <=> $a['revenue'];
            }
            return $b['enrollment_count'] <=> $a['enrollment_count'];
        });

        $top = array_slice($items, 0, $limit);
        foreach ($top as $idx => &$item) {
            $item['rank'] = $idx + 1;
        }

        return ApiResponse::success(
            $top,
            'Lấy top khóa học theo doanh thu thành công.'
        );
    }

    public function revenueSummary(\Illuminate\Http\Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $period = $this->resolvePeriod($request->all());

        $summaryQuery = \Illuminate\Support\Facades\DB::table('revenues')
            ->join('courses', 'courses.id', '=', 'revenues.course_id')
            ->where(function ($q) use ($instructorId): void {
                $q->where('revenues.instructor_id', $instructorId)
                  ->orWhere('courses.instructor_id', $instructorId);
            })
            ->whereNull('courses.deleted_at')
            ->whereIn('revenues.status', ['pending', 'available', 'scheduled', 'included_in_payout', 'paid', 'withdrawn']);

        if ($period['preset'] !== 'all' && $period['preset'] !== 'all_time') {
            $summaryQuery->whereBetween('revenues.earned_at', [$period['current_from'], $period['current_to']]);
        }

        if ($request->query('course_id') && $request->query('course_id') !== 'all') {
            $summaryQuery->where('revenues.course_id', (int) $request->query('course_id'));
        }

        $row = (clone $summaryQuery)
            ->selectRaw('
                COALESCE(SUM(gross_amount), 0) as gross_amount,
                COALESCE(SUM(instructor_amount), 0) as instructor_amount,
                COALESCE(SUM(platform_fee_amount), 0) as platform_fee_amount
            ')
            ->first();

        $hasSaleSource = \Illuminate\Support\Facades\Schema::hasColumn('revenues', 'sale_source');

        if ($hasSaleSource) {
            $breakdownRows = (clone $summaryQuery)
                ->selectRaw('
                    COALESCE(sale_source, "marketplace_default") as sale_source,
                    COALESCE(SUM(gross_amount), 0) as gross_amount,
                    COALESCE(SUM(instructor_amount), 0) as instructor_amount,
                    COALESCE(SUM(platform_fee_amount), 0) as platform_fee_amount
                ')
                ->groupBy(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_source, "marketplace_default")'))
                ->get();
        } else {
            $breakdownRows = collect([
                (object) [
                    'sale_source' => 'marketplace_default',
                    'gross_amount' => $row->gross_amount ?? 0,
                    'instructor_amount' => $row->instructor_amount ?? 0,
                    'platform_fee_amount' => $row->platform_fee_amount ?? 0,
                ],
            ]);
        }

        $labels = [
            'marketplace_default' => 'Marketplace mặc định',
            'platform_ads' => 'Quảng cáo nền tảng',
            'admin_campaign' => 'Chiến dịch admin',
            'instructor_coupon' => 'Mã giảm giá giảng viên',
            'instructor_referral' => 'Link giới thiệu giảng viên',
        ];

        $sourceBreakdown = $breakdownRows->map(function ($row) use ($labels) {
            $source = $row->sale_source;
            return [
                'sale_source' => $source,
                'sale_source_label' => $labels[$source] ?? 'Marketplace mặc định',
                'gross_revenue' => number_format((float) $row->gross_amount, 2, '.', ''),
                'instructor_revenue' => number_format((float) $row->instructor_amount, 2, '.', ''),
                'platform_fee' => number_format((float) $row->platform_fee_amount, 2, '.', ''),
            ];
        })->all();

        // Previous period totals for comparison
        $prevQuery = \Illuminate\Support\Facades\DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->whereBetween('earned_at', [$period['previous_from'], $period['previous_to']])
            ->whereIn('status', ['pending', 'available', 'scheduled', 'included_in_payout', 'paid', 'withdrawn']);

        if ($request->query('course_id') && $request->query('course_id') !== 'all') {
            $prevQuery->where('course_id', (int) $request->query('course_id'));
        }

        $prevRow = $prevQuery
            ->selectRaw('
                COALESCE(SUM(gross_amount), 0) as gross_amount,
                COALESCE(SUM(instructor_amount), 0) as instructor_amount,
                COALESCE(SUM(platform_fee_amount), 0) as platform_fee_amount
            ')
            ->first();

        // Calculate withdrawable balance
        $withdrawalRepo = app(\App\Repositories\Instructor\InstructorWithdrawalRepository::class);
        $withdrawalSummary = $withdrawalRepo->getSummary($instructorId);

        $currGross = (float) ($row->gross_amount ?? 0);
        $currInst = (float) ($row->instructor_amount ?? 0);
        $currFee = (float) ($row->platform_fee_amount ?? 0);

        $prevGross = (float) ($prevRow->gross_amount ?? 0);
        $prevInst = (float) ($prevRow->instructor_amount ?? 0);
        $prevFee = (float) ($prevRow->platform_fee_amount ?? 0);

        $grossChange = $this->calculatePercentChange($currGross, $prevGross);
        $instChange = $this->calculatePercentChange($currInst, $prevInst);
        $feeChange = $this->calculatePercentChange($currFee, $prevFee);

        return ApiResponse::success([
            'gross_revenue' => $currGross,
            'instructor_revenue' => $currInst,
            'platform_fee' => $currFee,
            'period_revenue' => $currInst,
            'withdrawable_balance' => (float) ($withdrawalSummary['available_balance'] ?? 0),
            'gross_amount' => number_format($currGross, 2, '.', ''),
            'instructor_amount' => number_format($currInst, 2, '.', ''),
            'platform_fee_amount' => number_format($currFee, 2, '.', ''),
            'comparison' => [
                'gross_percent' => $grossChange,
                'instructor_percent' => $instChange,
                'platform_fee_percent' => $feeChange,
                'period_revenue_percent' => $instChange,
                'gross_previous' => $prevGross,
                'instructor_previous' => $prevInst,
            ],
            'period' => [
                'preset' => $period['preset'],
                'from' => $period['current_from']->format('Y-m-d'),
                'to' => $period['current_to']->format('Y-m-d'),
                'previous_from' => $period['previous_from']->format('Y-m-d'),
                'previous_to' => $period['previous_to']->format('Y-m-d'),
            ],
            'source_breakdown' => $sourceBreakdown,
        ], 'Lấy tổng quan doanh thu thành công.');
    }

    public function revenueDetails(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $period = $this->resolvePeriod($request->all());

        $page = max((int) $request->query('page', 1), 1);
        $perPage = min(max((int) $request->query('per_page', 5), 1), 100);

        $query = \Illuminate\Support\Facades\DB::table('revenues')
            ->join('courses', 'courses.id', '=', 'revenues.course_id')
            ->where(function ($q) use ($instructorId): void {
                $q->where('revenues.instructor_id', $instructorId)
                  ->orWhere('courses.instructor_id', $instructorId);
            })
            ->whereNull('courses.deleted_at');

        if ($period['preset'] !== 'all' && $period['preset'] !== 'all_time') {
            $query->whereBetween('revenues.earned_at', [$period['current_from'], $period['current_to']]);
        }

        if ($request->query('course_id') && $request->query('course_id') !== 'all') {
            $query->where('revenues.course_id', (int) $request->query('course_id'));
        }

        if ($request->query('status') && $request->query('status') !== 'all') {
            $query->where('revenues.status', $request->query('status'));
        }

        if ($request->query('search')) {
            $search = '%' . trim($request->query('search')) . '%';
            $query->where('courses.title', 'like', $search);
        }

        $total = $query->count();

        $items = $query->select([
                'revenues.id',
                'revenues.earned_at',
                'revenues.gross_amount',
                'revenues.instructor_amount',
                'revenues.platform_fee_amount',
                'revenues.status',
                'courses.id as course_id',
                'courses.title as course_title',
            ])
            ->orderByDesc('revenues.earned_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(function ($row) {
                $statusLabel = 'Hoàn thành';
                if ($row->status === 'pending') $statusLabel = 'Chờ đối soát';
                if ($row->status === 'refunded') $statusLabel = 'Đã hoàn tiền';

                return [
                    'id' => (string) $row->id,
                    'date' => \Illuminate\Support\Carbon::parse($row->earned_at)->format('d/m/Y H:i'),
                    'course' => [
                        'id' => (int) $row->course_id,
                        'title' => $row->course_title,
                    ],
                    'orders' => 1,
                    'gross' => (float) $row->gross_amount,
                    'net' => (float) $row->instructor_amount,
                    'platform_fee' => (float) $row->platform_fee_amount,
                    'status' => $statusLabel,
                    'raw_status' => $row->status,
                ];
            });

        return ApiResponse::success([
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage) ?: 1,
                'per_page' => $perPage,
                'total' => $total,
                'from' => $total > 0 ? ($page - 1) * $perPage + 1 : 0,
                'to' => min($page * $perPage, $total),
            ]
        ], 'Lấy chi tiết doanh thu thành công.');
    }

    public function exportRevenues(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $instructorId = (int) $request->user()->id;
        $period = $this->resolvePeriod($request->all());

        $query = \Illuminate\Support\Facades\DB::table('revenues')
            ->join('courses', 'courses.id', '=', 'revenues.course_id')
            ->where(function ($q) use ($instructorId): void {
                $q->where('revenues.instructor_id', $instructorId)
                  ->orWhere('courses.instructor_id', $instructorId);
            })
            ->whereNull('courses.deleted_at');

        if ($period['preset'] !== 'all' && $period['preset'] !== 'all_time') {
            $query->whereBetween('revenues.earned_at', [$period['current_from'], $period['current_to']]);
        }

        $records = $query->select([
                'revenues.id',
                'revenues.earned_at',
                'revenues.gross_amount',
                'revenues.instructor_amount',
                'revenues.platform_fee_amount',
                'revenues.status',
                'courses.title as course_title',
            ])
            ->orderByDesc('revenues.earned_at')
            ->get();

        $dateFromStr = $period['current_from']->format('Y-m-d');
        $dateToStr = $period['current_to']->format('Y-m-d');
        $filename = 'doanh-thu-' . $dateFromStr . '-den-' . $dateToStr . '.csv';

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['ID Giao Dịch', 'Ngày Ghi Nhận', 'Tên Khóa Học', 'Doanh Thu Gộp (VND)', 'Thu Nhập Giảng Viên (VND)', 'Phí Nền Tảng (VND)', 'Trạng Thái']);

            foreach ($records as $row) {
                $statusLabel = 'Hoàn thành';
                if ($row->status === 'pending') $statusLabel = 'Chờ đối soát';
                if ($row->status === 'refunded') $statusLabel = 'Đã hoàn tiền';

                fputcsv($handle, [
                    $row->id,
                    $row->earned_at,
                    $row->course_title,
                    number_format((float)$row->gross_amount, 0, ',', '.'),
                    number_format((float)$row->instructor_amount, 0, ',', '.'),
                    number_format((float)$row->platform_fee_amount, 0, ',', '.'),
                    $statusLabel,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolvePeriod(array $filters): array
    {
        $preset = $filters['preset'] ?? $filters['period'] ?? 'month';
        $now = now();

        if ($preset === 'all' || $preset === 'all_time') {
            $currentFrom = \Illuminate\Support\Carbon::create(2020, 1, 1)->startOfDay();
            $currentTo = $now->copy()->addYears(10)->endOfDay();
            $prevFrom = $currentFrom->copy();
            $prevTo = $currentTo->copy();
        } elseif ($preset === 'day' || $preset === 'today' || $preset === '1d') {
            $currentFrom = $now->copy()->startOfDay();
            $currentTo = $now->copy()->endOfDay();
            $prevFrom = $now->copy()->subDay()->startOfDay();
            $prevTo = $now->copy()->subDay()->endOfDay();
        } elseif ($preset === '7d' || $preset === 'week') {
            $currentFrom = $now->copy()->subDays(6)->startOfDay();
            $currentTo = $now->copy()->endOfDay();
            $prevFrom = $currentFrom->copy()->subDays(7)->startOfDay();
            $prevTo = $currentFrom->copy()->subDays(1)->endOfDay();
        } elseif ($preset === '30d') {
            $currentFrom = $now->copy()->subDays(29)->startOfDay();
            $currentTo = $now->copy()->endOfDay();
            $prevFrom = $currentFrom->copy()->subDays(30)->startOfDay();
            $prevTo = $currentFrom->copy()->subDays(1)->endOfDay();
        } elseif ($preset === '90d' || $preset === 'quarter') {
            $currentFrom = $now->copy()->subDays(89)->startOfDay();
            $currentTo = $now->copy()->endOfDay();
            $prevFrom = $currentFrom->copy()->subDays(90)->startOfDay();
            $prevTo = $currentFrom->copy()->subDays(1)->endOfDay();
        } elseif ($preset === 'last_month') {
            $currentFrom = $now->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay();
            $currentTo = $now->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay();
            $prevFrom = $now->copy()->subMonthsNoOverflow(2)->startOfMonth()->startOfDay();
            $prevTo = $now->copy()->subMonthsNoOverflow(2)->endOfMonth()->endOfDay();
        } elseif ($preset === 'this_year' || $preset === 'year') {
            $currentFrom = $now->copy()->startOfYear();
            $currentTo = $now->copy()->endOfYear();
            $prevFrom = $now->copy()->subYear()->startOfYear();
            $prevTo = $now->copy()->subYear()->endOfYear();
        } elseif ($preset === 'custom' && !empty($filters['date_from']) && !empty($filters['date_to'])) {
            $currentFrom = \Illuminate\Support\Carbon::parse($filters['date_from'])->startOfDay();
            $currentTo = \Illuminate\Support\Carbon::parse($filters['date_to'])->endOfDay();
            $days = max(1, (int) $currentFrom->diffInDays($currentTo) + 1);
            $prevTo = $currentFrom->copy()->subDay()->endOfDay();
            $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();
        } else {
            $preset = 'month';
            $currentFrom = $now->copy()->startOfMonth();
            $currentTo = $now->copy()->endOfMonth();
            $prevFrom = $now->copy()->subMonth()->startOfMonth();
            $prevTo = $now->copy()->subMonth()->endOfMonth();
        }

        return [
            'preset' => $preset,
            'current_from' => $currentFrom,
            'current_to' => $currentTo,
            'previous_from' => $prevFrom,
            'previous_to' => $prevTo,
        ];
    }

    private function calculatePercentChange(float $current, float $previous): ?float
    {
        if ($current == 0 && $previous == 0) {
            return 0.0;
        }
        if ($previous == 0) {
            return null;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
