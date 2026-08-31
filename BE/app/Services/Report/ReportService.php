<?php

namespace App\Services\Report;

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportService
{
    public function getTopCoursesReport(array $filters)
    {
        $hasDateFilter = !empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['month']) || !empty($filters['year']);

        $eMaxQuery = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->whereIn('enrollments.status', ['active', 'completed'])
            ->selectRaw('COUNT(enrollments.id) as count')
            ->groupBy('courses.id')
            ->orderByDesc('count');

        if (!empty($filters['date_from'])) {
            $eMaxQuery->whereDate('enrollments.enrolled_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $eMaxQuery->whereDate('enrollments.enrolled_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $eMaxQuery->whereMonth('enrollments.enrolled_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $eMaxQuery->whereYear('enrollments.enrolled_at', $filters['year']);
        }

        $eMax = (float) $eMaxQuery->value('count');

        if ($eMax == 0) {
            $paginator = new LengthAwarePaginator([], 0, (int) ($filters['per_page'] ?? 15), 1);
            return [
                'paginator' => $paginator,
                'summary' => [
                    'total_courses' => 0,
                    'total_sold' => 0,
                    'total_revenue' => 0,
                    'total_completed' => 0,
                ],
            ];
        }

        $query = Course::query()
            ->select('courses.id', 'courses.title', 'courses.slug', 'courses.status', 'courses.price', 'courses.sale_price', 'courses.instructor_id')
            ->with(['instructor:id,full_name,email,role,status']);

        if (!empty($filters['course_id'])) {
            $query->where('courses.id', $filters['course_id']);
        }

        $orderQuery = DB::table('orders')
            ->leftJoin('revenues', 'orders.id', '=', 'revenues.order_id')
            ->select('orders.course_id')
            ->selectRaw('COUNT(orders.id) as sold_count')
            ->selectRaw('COALESCE(SUM(revenues.gross_amount), SUM(orders.amount)) as total_revenue')
            ->selectRaw('MAX(orders.paid_at) as last_paid_at')
            ->where('orders.status', 'paid')
            ->where('orders.payment_status', 'paid')
            ->groupBy('orders.course_id');

        if (!empty($filters['date_from'])) {
            $orderQuery->whereDate('orders.paid_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $orderQuery->whereDate('orders.paid_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $orderQuery->whereMonth('orders.paid_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $orderQuery->whereYear('orders.paid_at', $filters['year']);
        }

        $enrollmentQuery = DB::table('enrollments')
            ->select('course_id')
            ->selectRaw('COUNT(id) as enrollment_count')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw("COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM lesson_progress WHERE lesson_progress.enrollment_id = enrollments.id) THEN enrollments.id ELSE NULL END) as started_count")
            ->whereIn('status', ['active', 'completed'])
            ->groupBy('course_id');

        if (!empty($filters['date_from'])) {
            $enrollmentQuery->whereDate('enrolled_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $enrollmentQuery->whereDate('enrolled_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $enrollmentQuery->whereMonth('enrolled_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $enrollmentQuery->whereYear('enrolled_at', $filters['year']);
        }

        $ratingQuery = DB::table('course_reviews')
            ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
            ->select('orders.course_id')
            ->selectRaw('AVG(course_reviews.rating) as average_rating')
            ->groupBy('orders.course_id');

        if (!empty($filters['date_from'])) {
            $ratingQuery->whereDate('course_reviews.created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $ratingQuery->whereDate('course_reviews.created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $ratingQuery->whereMonth('course_reviews.created_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $ratingQuery->whereYear('course_reviews.created_at', $filters['year']);
        }

        $query->leftJoinSub($orderQuery, 'o', function ($join) {
            $join->on('courses.id', '=', 'o.course_id');
        });

        $query->leftJoinSub($enrollmentQuery, 'e', function ($join) {
            $join->on('courses.id', '=', 'e.course_id');
        });

        $query->leftJoinSub($ratingQuery, 'r', function ($join) {
            $join->on('courses.id', '=', 'r.course_id');
        });

        $query->addSelect([
            DB::raw('COALESCE(o.sold_count, 0) as sold_count'),
            DB::raw('COALESCE(o.total_revenue, 0) as total_revenue'),
            'o.last_paid_at',
            DB::raw('COALESCE(e.enrollment_count, 0) as enrollment_count'),
            DB::raw('COALESCE(e.started_count, 0) as started_count'),
            DB::raw('COALESCE(e.completed_count, 0) as completed_count'),
            DB::raw('COALESCE(r.average_rating, 0) as average_rating'),
        ]);

        $eMaxLiteral = (float) $eMax;
        $query->selectRaw("
            (0.4 * (COALESCE(e.enrollment_count, 0) / {$eMaxLiteral})) +
            (0.4 * (CASE WHEN COALESCE(e.started_count, 0) > 0 THEN COALESCE(e.completed_count, 0) / e.started_count ELSE 0 END)) +
            (0.2 * (COALESCE(r.average_rating, 0) / 5)) as trending_score
        ");

        $sortBy = $filters['sort_by'] ?? 'trending_score';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if ($hasDateFilter) {
            $query->having('trending_score', '>', 0);
        } else {
            $query->whereRaw('COALESCE(e.enrollment_count, 0) >= 10');
            $query->having('trending_score', '>', 0);
        }

        if ($sortBy === 'completion_rate') {
            $query->orderByRaw('CASE WHEN COALESCE(e.started_count, 0) > 0 THEN (COALESCE(e.completed_count, 0) * 100.0 / e.started_count) ELSE 0 END ' . $sortDirection);
        } elseif ($sortBy === 'trending_score') {
            $query->orderBy('trending_score', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $query->orderBy('courses.id', 'desc');

        $perPage = max(1, (int) ($filters['per_page'] ?? 15));
        $paginator = $query->paginate($perPage);

        $summaryOrderQuery = DB::table('orders')
            ->where('status', 'paid')
            ->where('payment_status', 'paid');

        $summaryEnrollmentQuery = DB::table('enrollments')
            ->whereIn('status', ['active', 'completed']);

        if (!empty($filters['date_from'])) {
            $summaryOrderQuery->whereDate('paid_at', '>=', $filters['date_from']);
            $summaryEnrollmentQuery->whereDate('enrolled_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $summaryOrderQuery->whereDate('paid_at', '<=', $filters['date_to']);
            $summaryEnrollmentQuery->whereDate('enrolled_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $summaryOrderQuery->whereMonth('paid_at', $filters['month']);
            $summaryEnrollmentQuery->whereMonth('enrolled_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $summaryOrderQuery->whereYear('paid_at', $filters['year']);
            $summaryEnrollmentQuery->whereYear('enrolled_at', $filters['year']);
        }
        if (!empty($filters['course_id'])) {
            $summaryOrderQuery->where('course_id', $filters['course_id']);
            $summaryEnrollmentQuery->where('course_id', $filters['course_id']);
        }

        $summary = [
            'total_courses' => (int) $paginator->total(),
            'total_sold'    => (int) $summaryOrderQuery->count(),
            'total_revenue' => (float) $summaryOrderQuery->sum('amount'),
            'total_completed' => (int) $summaryEnrollmentQuery->where('status', 'completed')->count(),
        ];

        return [
            'paginator' => $paginator,
            'summary' => $summary,
        ];
    }

    public function getTopInstructorsReport(array $filters)
    {
        $enrollmentPeriodQuery = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->whereIn('enrollments.status', ['active', 'completed'])
            ->selectRaw('COUNT(enrollments.id) as count')
            ->groupBy('courses.instructor_id')
            ->orderByDesc('count');

        if (!empty($filters['date_from'])) {
            $enrollmentPeriodQuery->whereDate('enrollments.enrolled_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $enrollmentPeriodQuery->whereDate('enrollments.enrolled_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $enrollmentPeriodQuery->whereMonth('enrollments.enrolled_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $enrollmentPeriodQuery->whereYear('enrollments.enrolled_at', $filters['year']);
        }

        $teMax = (float) $enrollmentPeriodQuery->value('count');

        if ($teMax == 0) {
            $paginator = new LengthAwarePaginator([], 0, (int) ($filters['per_page'] ?? 15), 1);
            return [
                'paginator' => $paginator,
            ];
        }

        $query = User::query()
            ->select('users.id', 'users.full_name', 'users.email', 'users.role', 'users.status')
            ->where('users.role', 'instructor');

        if (!empty($filters['course_id'])) {
            $query->whereHas('courses', function ($q) use ($filters) {
                $q->where('id', $filters['course_id']);
            });
        }

        $courseQuery = DB::table('courses')
            ->select('instructor_id')
            ->selectRaw('COUNT(id) as total_courses')
            ->selectRaw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_courses")
            ->groupBy('instructor_id');

        if (!empty($filters['course_id'])) {
            $courseQuery->where('id', $filters['course_id']);
        }

        $revenueQuery = DB::table('revenues')
            ->join('courses', 'revenues.course_id', '=', 'courses.id')
            ->select('courses.instructor_id')
            ->selectRaw('COUNT(revenues.id) as total_sold')
            ->selectRaw('SUM(revenues.gross_amount) as total_revenue')
            ->selectRaw('SUM(revenues.instructor_amount) as instructor_amount')
            ->selectRaw('SUM(revenues.platform_fee_amount) as platform_fee_amount')
            ->selectRaw('MAX(revenues.earned_at) as last_activity_at')
            ->groupBy('courses.instructor_id');

        if (!empty($filters['course_id'])) {
            $revenueQuery->where('revenues.course_id', $filters['course_id']);
        }
        if (!empty($filters['date_from'])) {
            $revenueQuery->whereDate('revenues.earned_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $revenueQuery->whereDate('revenues.earned_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $revenueQuery->whereMonth('revenues.earned_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $revenueQuery->whereYear('revenues.earned_at', $filters['year']);
        }

        $enrollmentQuery = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->select('courses.instructor_id')
            ->selectRaw('COUNT(enrollments.id) as total_enrollments')
            ->selectRaw("SUM(CASE WHEN enrollments.status = 'completed' THEN 1 ELSE 0 END) as total_completed")
            ->selectRaw("COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM lesson_progress WHERE lesson_progress.enrollment_id = enrollments.id) THEN enrollments.id ELSE NULL END) as total_started")
            ->whereIn('enrollments.status', ['active', 'completed'])
            ->groupBy('courses.instructor_id');

        if (!empty($filters['course_id'])) {
            $enrollmentQuery->where('enrollments.course_id', $filters['course_id']);
        }
        if (!empty($filters['date_from'])) {
            $enrollmentQuery->whereDate('enrollments.enrolled_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $enrollmentQuery->whereDate('enrollments.enrolled_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $enrollmentQuery->whereMonth('enrollments.enrolled_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $enrollmentQuery->whereYear('enrollments.enrolled_at', $filters['year']);
        }

        $query->leftJoinSub($courseQuery, 'c', function ($join) {
            $join->on('users.id', '=', 'c.instructor_id');
        });

        $query->leftJoinSub($revenueQuery, 'r', function ($join) {
            $join->on('users.id', '=', 'r.instructor_id');
        });

        $query->leftJoinSub($enrollmentQuery, 'e', function ($join) {
            $join->on('users.id', '=', 'e.instructor_id');
        });

        $query->addSelect([
            DB::raw('COALESCE(c.total_courses, 0) as total_courses'),
            DB::raw('COALESCE(c.published_courses, 0) as published_courses'),
            DB::raw('COALESCE(r.total_sold, 0) as total_sold'),
            DB::raw('COALESCE(r.total_revenue, 0) as total_revenue'),
            DB::raw('COALESCE(r.instructor_amount, 0) as instructor_amount'),
            DB::raw('COALESCE(r.platform_fee_amount, 0) as platform_fee_amount'),
            'r.last_activity_at',
            DB::raw('COALESCE(e.total_enrollments, 0) as total_enrollments'),
            DB::raw('COALESCE(e.total_started, 0) as total_started'),
            DB::raw('COALESCE(e.total_completed, 0) as total_completed'),
        ]);

        $query->selectRaw("
            (0.6 * (COALESCE(e.total_enrollments, 0) / ?)) + 
            (0.4 * (CASE WHEN COALESCE(e.total_started, 0) > 0 THEN COALESCE(e.total_completed, 0) / e.total_started ELSE 0 END)) as trending_score
        ", [$teMax]);

        $sortBy = $filters['sort_by'] ?? 'trending_score';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->having('trending_score', '>', 0);

        if ($sortBy === 'completion_rate') {
            $query->orderByRaw('CASE WHEN COALESCE(e.total_started, 0) > 0 THEN (COALESCE(e.total_completed, 0) * 100.0 / e.total_started) ELSE 0 END ' . $sortDirection);
        } elseif ($sortBy === 'trending_score') {
            $query->orderBy('trending_score', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $query->orderBy('users.id', 'desc');

        $perPage = max(1, (int) ($filters['per_page'] ?? 15));
        $paginator = $query->paginate($perPage);

        return [
            'paginator' => $paginator,
        ];
    }

    public function getInactiveLearnersReport(int $instructorId, array $filters)
    {
        $inactiveDays = (int) ($filters['inactive_days'] ?? config('report.inactive_learner_days', 14));
        $cutoffDate = Carbon::now()->subDays($inactiveDays);

        $activityQuery = DB::table('lesson_progress as lp')
            ->join('enrollments as e', 'lp.enrollment_id', '=', 'e.id')
            ->select('e.id as enrollment_id')
            ->selectRaw('MAX(COALESCE(lp.last_accessed_at, lp.updated_at)) as max_lp_activity')
            ->groupBy('e.id');

        $dailyActivityQuery = DB::table('learning_daily_activity as lda')
            ->where('lda.video_learning_seconds', '>', 0)
            ->select('lda.enrollment_id')
            ->selectRaw('MAX(lda.activity_date) as max_da_activity')
            ->groupBy('lda.enrollment_id');

        $query = DB::table('enrollments')
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->leftJoinSub($activityQuery, 'act', function ($join) {
                $join->on('enrollments.id', '=', 'act.enrollment_id');
            })
            ->leftJoinSub($dailyActivityQuery, 'da', function ($join) {
                $join->on('enrollments.id', '=', 'da.enrollment_id');
            })
            ->select(
                'users.id as learner_id',
                'users.full_name',
                'users.email',
                'users.phone',
                'courses.id as course_id',
                'courses.title as course_title',
                'courses.slug as course_slug',
                'enrollments.id as enrollment_id',
                'enrollments.status as enrollment_status',
                'enrollments.progress_percent',
                'enrollments.enrolled_at',
                DB::raw('COALESCE(da.max_da_activity, act.max_lp_activity, enrollments.last_accessed_at, enrollments.enrolled_at, enrollments.created_at) as last_activity_at')
            )
            ->where('courses.instructor_id', $instructorId);

        // Exclude Expired Trial
        $query->where(function ($q) {
            $q->whereNull('enrollments.expires_at')
              ->orWhere('enrollments.expires_at', '>', now());
        });

        // Base condition for incomplete
        $query->where('enrollments.status', '!=', 'completed');

        // Condition: enrollment age >= 14 days and last_activity <= cutoffDate
        $query->where(function ($q) use ($cutoffDate) {
            $q->where('enrollments.enrolled_at', '<=', $cutoffDate)
              ->orWhere('enrollments.created_at', '<=', $cutoffDate);
        });

        $query->where(function ($q) use ($cutoffDate) {
            $q->where(DB::raw('COALESCE(da.max_da_activity, act.max_lp_activity, enrollments.last_accessed_at, enrollments.enrolled_at, enrollments.created_at)'), '<=', $cutoffDate);
        });

        if (!empty($filters['course_id'])) {
            $query->where('enrollments.course_id', $filters['course_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('enrollments.enrolled_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('enrollments.enrolled_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $query->whereMonth('enrollments.enrolled_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $query->whereYear('enrollments.enrolled_at', $filters['year']);
        }
        if (!empty($filters['status'])) {
            $query->where('enrollments.status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'last_activity_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if ($sortBy === 'inactive_days') {
            $realDirection = strtolower($sortDirection) === 'desc' ? 'asc' : 'desc';
            $query->orderBy('last_activity_at', $realDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $query->orderBy('enrollments.id', 'desc');

        $perPage = max(1, (int) ($filters['per_page'] ?? 15));
        $paginator = $query->paginate($perPage);

        return [
            'paginator' => $paginator,
        ];
    }

    public function getSystemDashboard(array $filters): array
    {
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;
        $courseId = $filters['course_id'] ?? null;

        $applyDateFilter = function ($query, $column) use ($dateFrom, $dateTo, $month, $year) {
            if ($dateFrom) $query->whereDate($column, '>=', $dateFrom);
            if ($dateTo) $query->whereDate($column, '<=', $dateTo);
            if ($month) $query->whereMonth($column, $month);
            if ($year) $query->whereYear($column, $year);
        };

        // Users
        $userQuery = DB::table('users');
        $applyDateFilter($userQuery, 'created_at');

        $totalUsers = (clone $userQuery)->count();
        $totalLearners = (clone $userQuery)->where('role', 'learner')->count();
        $totalInstructors = (clone $userQuery)->where('role', 'instructor')->count();
        $userStatusCounts = (clone $userQuery)->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->toArray();

        // Courses
        $courseQuery = DB::table('courses');
        $applyDateFilter($courseQuery, 'created_at');
        if ($courseId) $courseQuery->where('id', $courseId);

        $totalCourses = (clone $courseQuery)->count();
        $totalPublishedCourses = (clone $courseQuery)->where('status', 'published')->count();
        $courseStatusCounts = (clone $courseQuery)->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->toArray();

        // Orders
        $orderQuery = DB::table('orders');
        $applyDateFilter($orderQuery, 'created_at');
        if ($courseId) $orderQuery->where('course_id', $courseId);

        $totalOrders = (clone $orderQuery)->count();

        // Paid Orders
        $paidOrderQuery = DB::table('orders')->where('status', 'paid');
        $applyDateFilter($paidOrderQuery, 'paid_at');
        if ($courseId) $paidOrderQuery->where('course_id', $courseId);

        $paidOrders = (clone $paidOrderQuery)->count();

        // Revenue from revenues snapshot
        $revenueQuery = DB::table('revenues');
        $applyDateFilter($revenueQuery, 'earned_at');
        if ($courseId) $revenueQuery->where('course_id', $courseId);

        $grossAmount = (float) ((clone $revenueQuery)->sum('gross_amount') ?? 0);
        $instructorAmount = (float) ((clone $revenueQuery)->sum('instructor_amount') ?? 0);
        $platformFeeAmount = (float) ((clone $revenueQuery)->sum('platform_fee_amount') ?? 0);
        $totalRevenue = $grossAmount;

        // Enrollments
        $enrollmentQuery = DB::table('enrollments');
        $applyDateFilter($enrollmentQuery, 'enrolled_at');
        if ($courseId) $enrollmentQuery->where('course_id', $courseId);

        $totalEnrollments = (clone $enrollmentQuery)->count();
        $completedEnrollments = (clone $enrollmentQuery)->where('status', 'completed')->count();
        $startedEnrollments = (clone $enrollmentQuery)->whereExists(function ($sq) {
            $sq->select(DB::raw(1))
                ->from('lesson_progress')
                ->whereColumn('lesson_progress.enrollment_id', 'enrollments.id');
        })->count();

        $completionRate = $startedEnrollments > 0 ? round(($completedEnrollments / $startedEnrollments) * 100, 2) : 0.0;

        // Recent orders
        $latestOrdersQuery = Order::query()
            ->with(['user:id,full_name', 'course:id,title,slug'])
            ->orderBy('id', 'desc')
            ->limit(5);
        if ($courseId) $latestOrdersQuery->where('course_id', $courseId);
        $latestOrders = $latestOrdersQuery->get();

        // Recent courses
        $latestCoursesQuery = Course::query()
            ->with(['instructor:id,full_name'])
            ->orderBy('id', 'desc')
            ->limit(5);
        if ($courseId) $latestCoursesQuery->where('id', $courseId);
        $latestCourses = $latestCoursesQuery->get()->map(function ($c) {
            $c->instructor_name = $c->instructor?->full_name ?: 'N/A';
            return $c;
        });

        // Withdrawals statistics
        $withdrawalQuery = DB::table('withdraw_requests');
        $applyDateFilter($withdrawalQuery, 'created_at');

        $pendingWithdrawRequests = (clone $withdrawalQuery)->where('status', 'pending')->get();
        $approvedWithdrawRequests = (clone $withdrawalQuery)->where('status', 'approved')->get();
        $paidWithdrawRequests = (clone $withdrawalQuery)->where('status', 'paid')->get();

        $pendingWithdrawCount = $pendingWithdrawRequests->count();
        $approvedWithdrawCount = $approvedWithdrawRequests->count();
        $pendingWithdrawAmount = $pendingWithdrawRequests->sum('amount');
        $approvedWithdrawAmount = $approvedWithdrawRequests->sum('amount');
        $paidWithdrawAmount = $paidWithdrawRequests->sum('amount');

        // Action required backlog
        $pendingCourseReviews = DB::table('courses')->where('status', 'pending_review')->count();

        $pendingInstructorUpgrades = DB::table('payout_accounts')
            ->join('users', 'payout_accounts.user_id', '=', 'users.id')
            ->join('instructor_profiles', 'instructor_profiles.user_id', '=', 'users.id')
            ->where('payout_accounts.status', 'pending_verification')
            ->where('users.role', 'learner')
            ->count();

        $pendingPayoutAccounts = DB::table('payout_accounts')
            ->join('users', 'payout_accounts.user_id', '=', 'users.id')
            ->where('payout_accounts.status', 'pending_verification')
            ->where('users.role', 'instructor')
            ->count();

        $pendingWithdrawals = DB::table('withdraw_requests')->where('status', 'pending')->count();

        return [
            'summary' => [
                'total_users' => $totalUsers,
                'total_learners' => $totalLearners,
                'total_instructors' => $totalInstructors,
                'total_courses' => $totalCourses,
                'total_published_courses' => $totalPublishedCourses,
                'total_orders' => $totalOrders,
                'paid_orders' => $paidOrders,
                'total_revenue' => (float) $totalRevenue,
                'total_enrollments' => $totalEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => (float) $completionRate,
            ],
            'revenue' => [
                'gross_amount' => (float) $grossAmount,
                'instructor_amount' => (float) $instructorAmount,
                'platform_fee_amount' => (float) $platformFeeAmount,
            ],
            'course_status' => [
                'draft' => $courseStatusCounts['draft'] ?? 0,
                'pending_review' => $courseStatusCounts['pending_review'] ?? 0,
                'approved' => $courseStatusCounts['approved'] ?? 0,
                'rejected' => $courseStatusCounts['rejected'] ?? 0,
                'published' => $courseStatusCounts['published'] ?? 0,
                'hidden' => $courseStatusCounts['hidden'] ?? 0,
            ],
            'user_status' => [
                'active' => $userStatusCounts['active'] ?? 0,
                'inactive' => $userStatusCounts['inactive'] ?? 0,
                'locked' => $userStatusCounts['locked'] ?? 0,
            ],
            'withdrawal_summary' => [
                'pending_count' => $pendingWithdrawCount,
                'approved_count' => $approvedWithdrawCount,
                'pending_amount' => (float) $pendingWithdrawAmount,
                'approved_amount' => (float) $approvedWithdrawAmount,
                'paid_amount' => (float) $paidWithdrawAmount,
            ],
            'action_required' => [
                'pending_course_reviews' => $pendingCourseReviews,
                'pending_instructor_upgrades' => $pendingInstructorUpgrades,
                'pending_withdrawals' => $pendingWithdrawals,
                'pending_payout_accounts' => $pendingPayoutAccounts,
            ],
            'recent' => [
                'latest_orders' => $latestOrders,
                'latest_courses' => $latestCourses,
            ]
        ];
    }

    public function getRevenueReport(array $filters): array
    {
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;
        $courseId = $filters['course_id'] ?? null;
        $instructorId = $filters['instructor_id'] ?? null;
        $groupBy = $filters['group_by'] ?? 'day';

        $sortBy = $filters['sort_by'] ?? 'date';
        $sortDirection = strtolower($filters['sort_direction'] ?? 'asc');

        $query = DB::table('revenues')
            ->join('courses', 'revenues.course_id', '=', 'courses.id');

        $dateColumn = 'revenues.earned_at';

        // Apply filters
        if ($dateFrom) $query->whereDate($dateColumn, '>=', $dateFrom);
        if ($dateTo) $query->whereDate($dateColumn, '<=', $dateTo);
        if ($month) $query->whereMonth($dateColumn, $month);
        if ($year) $query->whereYear($dateColumn, $year);
        if ($courseId) $query->where('revenues.course_id', $courseId);
        if ($instructorId) $query->where('revenues.instructor_id', $instructorId);

        // Calculate summary
        $summaryQuery = clone $query;
        $summary = [
            'total_gross_amount' => (float) $summaryQuery->sum('revenues.gross_amount'),
            'total_instructor_amount' => (float) $summaryQuery->sum('revenues.instructor_amount'),
            'total_platform_fee_amount' => (float) $summaryQuery->sum('revenues.platform_fee_amount'),
            'order_count' => (int) $summaryQuery->count('revenues.id'),
            'course_count' => (int) $summaryQuery->distinct()->count('revenues.course_id'),
            'instructor_count' => (int) $summaryQuery->distinct()->count('revenues.instructor_id'),
        ];

        // Grouping
        $dbConnection = DB::connection()->getDriverName();
        $dateFormat = '';

        if ($dbConnection === 'sqlite') {
            $dateFormat = $groupBy === 'month' ? "strftime('%Y-%m', $dateColumn)" : "strftime('%Y-%m-%d', $dateColumn)";
        } else {
            $dateFormat = $groupBy === 'month' ? "DATE_FORMAT($dateColumn, '%Y-%m')" : "DATE($dateColumn)";
        }

        $query->selectRaw("$dateFormat as period");

        $query->selectRaw('SUM(revenues.gross_amount) as gross_amount')
            ->selectRaw('SUM(revenues.instructor_amount) as instructor_amount')
            ->selectRaw('SUM(revenues.platform_fee_amount) as platform_fee_amount')
            ->selectRaw('COUNT(revenues.id) as order_count')
            ->selectRaw('COUNT(DISTINCT revenues.course_id) as course_count')
            ->selectRaw('COUNT(DISTINCT revenues.instructor_id) as instructor_count');

        $query->groupBy(DB::raw($dateFormat));

        $sortFieldMap = [
            'date' => 'period',
            'gross_amount' => 'gross_amount',
            'instructor_amount' => 'instructor_amount',
            'platform_fee_amount' => 'platform_fee_amount',
            'order_count' => 'order_count',
        ];

        $orderCol = $sortFieldMap[$sortBy] ?? 'period';
        $query->orderBy($orderCol, $sortDirection);

        $perPage = max(1, (int) ($filters['per_page'] ?? 15));
        $paginator = $query->paginate($perPage);

        return [
            'summary' => $summary,
            'paginator' => $paginator,
        ];
    }

    public function getInstructorCourseDashboard(int $instructorId, int $courseId, array $filters): array
    {
        $course = DB::table('courses')
            ->where('id', $courseId)
            ->first();

        if (!$course) {
            abort(404, 'Không tìm thấy dữ liệu.');
        }

        if ($course->instructor_id !== $instructorId) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Bạn không có quyền xem dữ liệu khóa học này.');
        }

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;

        $applyDateFilter = function ($query, $column) use ($dateFrom, $dateTo, $month, $year) {
            if ($dateFrom) $query->whereDate($column, '>=', $dateFrom);
            if ($dateTo) $query->whereDate($column, '<=', $dateTo);
            if ($month) $query->whereMonth($column, $month);
            if ($year) $query->whereYear($column, $year);
        };

        // Orders
        $ordersQuery = DB::table('orders')->where('course_id', $courseId);
        $applyDateFilter($ordersQuery, 'created_at');
        $totalOrders = (clone $ordersQuery)->count();

        $paidOrdersQuery = DB::table('orders')->where('course_id', $courseId)->where('status', 'paid');
        $applyDateFilter($paidOrdersQuery, 'paid_at');
        $paidOrders = (clone $paidOrdersQuery)->count();
        $latestOrderPaidAt = (clone $paidOrdersQuery)->max('paid_at');

        // Revenue from revenues snapshot
        $revenuesQuery = DB::table('revenues')->where('course_id', $courseId);
        $applyDateFilter($revenuesQuery, 'earned_at');
        $totalRevenue = (clone $revenuesQuery)->sum('gross_amount') ?? 0;
        $instructorRevenue = (clone $revenuesQuery)->sum('instructor_amount') ?? 0;
        $platformFee = (clone $revenuesQuery)->sum('platform_fee_amount') ?? 0;

        // Enrollments
        $enrollmentsQuery = DB::table('enrollments')->where('course_id', $courseId);
        $applyDateFilter($enrollmentsQuery, 'enrolled_at');

        $totalEnrollments = (clone $enrollmentsQuery)->count();
        $activeEnrollments = (clone $enrollmentsQuery)->where('status', 'active')->count();
        $completedEnrollments = (clone $enrollmentsQuery)->where('status', 'completed')->count();
        $startedEnrollments = (clone $enrollmentsQuery)->whereExists(function ($sq) {
            $sq->select(DB::raw(1))
                ->from('lesson_progress')
                ->whereColumn('lesson_progress.enrollment_id', 'enrollments.id');
        })->count();

        $completionRate = $startedEnrollments > 0 ? round(($completedEnrollments / $startedEnrollments) * 100, 2) : 0.0;
        $latestEnrollmentAccessedAt = (clone $enrollmentsQuery)->max('last_accessed_at');

        // Lessons: Only published lessons and published sections
        $totalLessons = DB::table('lessons')
            ->join('course_sections', 'lessons.course_section_id', '=', 'course_sections.id')
            ->where('lessons.course_id', $courseId)
            ->where('lessons.status', 'published')
            ->where('course_sections.status', 'published')
            ->count();

        // Lesson Progress
        $lessonProgressQuery = DB::table('lesson_progress')
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->where('lessons.course_id', $courseId)
            ->where('lessons.status', 'published');

        $applyDateFilter($lessonProgressQuery, 'lesson_progress.last_accessed_at');

        $completedLessonProgress = (clone $lessonProgressQuery)->where('lesson_progress.status', 'completed')->count();
        $latestLessonAccessedAt = (clone $lessonProgressQuery)->max('lesson_progress.last_accessed_at');

        // Latest activity overall
        $activities = array_filter([$latestOrderPaidAt, $latestEnrollmentAccessedAt, $latestLessonAccessedAt]);
        $latestActivityAt = empty($activities) ? null : max($activities);

        return [
            'course' => [
                'id' => (int) $course->id,
                'title' => $course->title,
                'slug' => $course->slug ?? null,
                'status' => $course->status ?? null,
            ],
            'summary' => [
                'total_orders' => (int) $totalOrders,
                'paid_orders' => (int) $paidOrders,
                'total_revenue' => (float) $totalRevenue,
                'instructor_revenue' => (float) $instructorRevenue,
                'platform_fee' => (float) $platformFee,
                'total_enrollments' => (int) $totalEnrollments,
                'active_enrollments' => (int) $activeEnrollments,
                'completed_enrollments' => (int) $completedEnrollments,
                'completion_rate' => (float) $completionRate,
                'total_lessons' => (int) $totalLessons,
                'completed_lesson_progress' => (int) $completedLessonProgress,
                'latest_activity_at' => $latestActivityAt,
            ],
            'revenue' => [
                'gross_amount' => (float) $totalRevenue,
                'instructor_amount' => (float) $instructorRevenue,
                'platform_fee_amount' => (float) $platformFee,
            ],
            'enrollment' => [
                'total' => (int) $totalEnrollments,
                'active' => (int) $activeEnrollments,
                'completed' => (int) $completedEnrollments,
                'completion_rate' => (float) $completionRate,
            ],
            'activity' => [
                'latest_order_paid_at' => $latestOrderPaidAt,
                'latest_enrollment_accessed_at' => $latestEnrollmentAccessedAt,
                'latest_lesson_accessed_at' => $latestLessonAccessedAt,
            ]
        ];
    }
}
