<?php

namespace App\Services\Report;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportService
{
    public function getTopCoursesReport(array $filters)
    {
        $hasDateFilter = !empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['month']) || !empty($filters['year']);

        $eMaxQuery = \Illuminate\Support\Facades\DB::table('enrollments')
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
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $filters['per_page'] ?? 15, 1);
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

        $query = \App\Models\Course::query()
            ->select('courses.id', 'courses.title', 'courses.slug', 'courses.status', 'courses.price', 'courses.sale_price', 'courses.instructor_id')
            ->with(['instructor:id,full_name,email,role,status']);

        if (!empty($filters['course_id'])) {
            $query->where('courses.id', $filters['course_id']);
        }

        $orderQuery = \Illuminate\Support\Facades\DB::table('orders')
            ->select('course_id')
            ->selectRaw('COUNT(id) as sold_count')
            ->selectRaw('SUM(amount) as total_revenue')
            ->selectRaw('MAX(paid_at) as last_paid_at')
            ->where('status', 'paid')
            ->where('payment_status', 'paid')
            ->groupBy('course_id');

        if (!empty($filters['date_from'])) {
            $orderQuery->whereDate('paid_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $orderQuery->whereDate('paid_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $orderQuery->whereMonth('paid_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $orderQuery->whereYear('paid_at', $filters['year']);
        }

        $enrollmentQuery = \Illuminate\Support\Facades\DB::table('enrollments')
            ->select('course_id')
            ->selectRaw('COUNT(id) as enrollment_count')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
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

        $ratingQuery = \Illuminate\Support\Facades\DB::table('course_reviews')
            ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
            ->select('orders.course_id')
            ->selectRaw('AVG(course_reviews.rating) as average_rating')
            ->whereNull('course_reviews.deleted_at')
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
            \Illuminate\Support\Facades\DB::raw('COALESCE(o.sold_count, 0) as sold_count'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(o.total_revenue, 0) as total_revenue'),
            'o.last_paid_at',
            \Illuminate\Support\Facades\DB::raw('COALESCE(e.enrollment_count, 0) as enrollment_count'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(e.completed_count, 0) as completed_count'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(r.average_rating, 0) as average_rating'),
        ]);

        $eMaxLiteral = (float) $eMax;
        $query->selectRaw("
            (0.4 * (COALESCE(e.enrollment_count, 0) / {$eMaxLiteral})) + 
            (0.4 * (CASE WHEN COALESCE(e.enrollment_count, 0) > 0 THEN COALESCE(e.completed_count, 0) / e.enrollment_count ELSE 0 END)) + 
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
            $query->orderByRaw('CASE WHEN COALESCE(e.enrollment_count, 0) > 0 THEN (COALESCE(e.completed_count, 0) * 100.0 / e.enrollment_count) ELSE 0 END ' . $sortDirection);
        } else if ($sortBy === 'trending_score') {
            $query->orderBy('trending_score', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $query->orderBy('courses.id', 'desc');

        $perPage = $filters['per_page'] ?? 15;
        $paginator = $query->paginate($perPage);

        $summary = [
            'total_courses' => $paginator->total(),
            'total_sold'    => 0,
            'total_revenue' => 0,
            'total_completed' => 0,
        ];

        // Build a fresh summary query to avoid PDO binding issues when cloning
        // a query that contains leftJoinSub subqueries with their own bindings.
        $summaryOrderQuery = \Illuminate\Support\Facades\DB::table('orders')
            ->where('status', 'paid')
            ->where('payment_status', 'paid');

        $summaryEnrollmentQuery = \Illuminate\Support\Facades\DB::table('enrollments')
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

        $summary['total_sold']    = (int)   $summaryOrderQuery->count();
        $summary['total_revenue'] = (float) $summaryOrderQuery->sum('amount');
        $summary['total_completed'] = (int) $summaryEnrollmentQuery->where('status', 'completed')->count();

        return [
            'paginator' => $paginator,
            'summary' => $summary,
        ];
    }

    public function getTopInstructorsReport(array $filters)
    {
        $hasDateFilter = !empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['month']) || !empty($filters['year']);

        $enrollmentPeriodQuery = \Illuminate\Support\Facades\DB::table('enrollments')
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
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $filters['per_page'] ?? 15, 1);
            return [
                'paginator' => $paginator,
            ];
        }

        $query = \App\Models\User::query()
            ->select('users.id', 'users.full_name', 'users.email', 'users.role', 'users.status')
            ->where('users.role', 'instructor');

        if (!empty($filters['course_id'])) {
            $query->whereHas('courses', function ($q) use ($filters) {
                $q->where('id', $filters['course_id']);
            });
        }

        $courseQuery = \Illuminate\Support\Facades\DB::table('courses')
            ->select('instructor_id')
            ->selectRaw('COUNT(id) as total_courses')
            ->selectRaw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_courses")
            ->whereNull('deleted_at')
            ->groupBy('instructor_id');

        if (!empty($filters['course_id'])) {
            $courseQuery->where('id', $filters['course_id']);
        }

        $revenueQuery = \Illuminate\Support\Facades\DB::table('orders')
            ->join('courses', 'orders.course_id', '=', 'courses.id')
            ->leftJoin('revenues', 'orders.id', '=', 'revenues.order_id')
            ->select('courses.instructor_id')
            ->selectRaw('COUNT(orders.id) as total_sold')
            ->selectRaw('SUM(orders.amount) as total_revenue')
            ->selectRaw('SUM(COALESCE(revenues.instructor_amount, orders.amount * 0.85)) as instructor_amount')
            ->selectRaw('SUM(COALESCE(revenues.platform_fee_amount, orders.amount * 0.15)) as platform_fee_amount')
            ->selectRaw('MAX(orders.paid_at) as last_activity_at')
            ->where('orders.status', 'paid')
            ->where('orders.payment_status', 'paid')
            ->groupBy('courses.instructor_id');

        if (!empty($filters['course_id'])) {
            $revenueQuery->where('orders.course_id', $filters['course_id']);
        }
        if (!empty($filters['date_from'])) {
            $revenueQuery->whereDate('orders.paid_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $revenueQuery->whereDate('orders.paid_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $revenueQuery->whereMonth('orders.paid_at', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $revenueQuery->whereYear('orders.paid_at', $filters['year']);
        }

        $enrollmentQuery = \Illuminate\Support\Facades\DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->select('courses.instructor_id')
            ->selectRaw('COUNT(enrollments.id) as total_enrollments')
            ->selectRaw("SUM(CASE WHEN enrollments.status = 'completed' THEN 1 ELSE 0 END) as total_completed")
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
            \Illuminate\Support\Facades\DB::raw('COALESCE(c.total_courses, 0) as total_courses'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(c.published_courses, 0) as published_courses'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(r.total_sold, 0) as total_sold'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(r.total_revenue, 0) as total_revenue'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(r.instructor_amount, 0) as instructor_amount'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(r.platform_fee_amount, 0) as platform_fee_amount'),
            'r.last_activity_at',
            \Illuminate\Support\Facades\DB::raw('COALESCE(e.total_enrollments, 0) as total_enrollments'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(e.total_completed, 0) as total_completed'),
        ]);

        $query->selectRaw("
            (0.6 * (COALESCE(e.total_enrollments, 0) / ?)) + 
            (0.4 * (CASE WHEN COALESCE(e.total_enrollments, 0) > 0 THEN COALESCE(e.total_completed, 0) / e.total_enrollments ELSE 0 END)) as trending_score
        ", [$teMax]);

        $sortBy = $filters['sort_by'] ?? 'trending_score';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->having('trending_score', '>', 0);

        if ($sortBy === 'completion_rate') {
            $query->orderByRaw('CASE WHEN COALESCE(e.total_enrollments, 0) > 0 THEN (COALESCE(e.total_completed, 0) * 100.0 / e.total_enrollments) ELSE 0 END ' . $sortDirection);
        } else if ($sortBy === 'trending_score') {
            $query->orderBy('trending_score', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $query->orderBy('users.id', 'desc');

        $perPage = $filters['per_page'] ?? 15;
        $paginator = $query->paginate($perPage);

        return [
            'paginator' => $paginator,
        ];
    }
    public function getInactiveLearnersReport(int $instructorId, array $filters)
    {
        $lessonProgressQuery = DB::table('lesson_progress')
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->select('lesson_progress.user_id', 'lessons.course_id')
            ->selectRaw('MAX(lesson_progress.last_accessed_at) as max_lesson_accessed_at')
            ->groupBy('lesson_progress.user_id', 'lessons.course_id');

        $query = DB::table('enrollments')
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->leftJoinSub($lessonProgressQuery, 'lp', function ($join) {
                $join->on('enrollments.user_id', '=', 'lp.user_id')
                    ->on('enrollments.course_id', '=', 'lp.course_id');
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
                DB::raw('COALESCE(lp.max_lesson_accessed_at, enrollments.last_accessed_at, enrollments.enrolled_at, enrollments.created_at) as last_activity_at')
            )
            ->where('courses.instructor_id', $instructorId)
            ;

        // Base condition for "bỏ dở": not completed
        $query->where(function ($q) {
            $q->where('enrollments.status', '!=', 'completed')
                ->orWhereNull('enrollments.completed_at');
        });

        // Filter inactive_days
        $inactiveDays = $filters['inactive_days'] ?? 14;
        $cutoffDate = now()->subDays($inactiveDays);

        $query->where(function ($q) use ($cutoffDate) {
            $q->where(DB::raw('COALESCE(lp.max_lesson_accessed_at, enrollments.last_accessed_at, enrollments.enrolled_at, enrollments.created_at)'), '<=', $cutoffDate)
                ->orWhereNull(DB::raw('COALESCE(lp.max_lesson_accessed_at, enrollments.last_accessed_at, enrollments.enrolled_at, enrollments.created_at)'));
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
            // Sorting by inactive_days DESC is sorting by last_activity_at ASC
            $realDirection = strtolower($sortDirection) === 'desc' ? 'asc' : 'desc';
            $query->orderBy('last_activity_at', $realDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $query->orderBy('enrollments.id', 'desc');

        $perPage = $filters['per_page'] ?? 15;
        $paginator = $query->paginate($perPage);

        return [
            'paginator' => $paginator,
        ];
    }

    public function getSystemDashboard(array $filters): array
    {
        // Setup Date Filters
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
        // Course ID doesn't filter total system users

        $totalUsers = (clone $userQuery)->count();
        $totalLearners = (clone $userQuery)->where('role', 'learner')->count();
        $totalInstructors = (clone $userQuery)->where('role', 'instructor')->count();
        $userStatusCounts = (clone $userQuery)->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->toArray();

        // Courses
        $courseQuery = DB::table('courses')->whereNull('deleted_at');
        $applyDateFilter($courseQuery, 'created_at');
        if ($courseId) $courseQuery->where('id', $courseId);

        $totalCourses = (clone $courseQuery)->count();
        $totalPublishedCourses = (clone $courseQuery)->where('status', 'published')->count();
        $courseStatusCounts = (clone $courseQuery)->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->toArray();

        // Orders
        $orderQuery = DB::table('orders');
        $applyDateFilter($orderQuery, 'created_at'); // Filter by created_at for total orders
        if ($courseId) $orderQuery->where('course_id', $courseId);

        $totalOrders = (clone $orderQuery)->count();

        // Paid Orders
        $paidOrderQuery = DB::table('orders')->where('status', 'paid');
        $applyDateFilter($paidOrderQuery, 'paid_at');
        if ($courseId) $paidOrderQuery->where('course_id', $courseId);

        $paidOrders = (clone $paidOrderQuery)->count();

        // Revenue
        $revenueQuery = DB::table('orders')
            ->leftJoin('revenues', 'orders.id', '=', 'revenues.order_id')
            ->where('orders.status', 'paid')
            ->where('orders.payment_status', 'paid');

        $applyDateFilter($revenueQuery, 'orders.paid_at');
        if ($courseId) $revenueQuery->where('orders.course_id', $courseId);

        $grossAmount = (clone $revenueQuery)->sum('orders.amount');
        $instructorAmount = (clone $revenueQuery)->sum(DB::raw('COALESCE(revenues.instructor_amount, orders.amount * 0.85)'));
        $platformFeeAmount = (clone $revenueQuery)->sum(DB::raw('COALESCE(revenues.platform_fee_amount, orders.amount * 0.15)'));
        $totalRevenue = $grossAmount;

        // Enrollments
        $enrollmentQuery = DB::table('enrollments');
        $applyDateFilter($enrollmentQuery, 'created_at'); // or enrolled_at if it exists, assume created_at works if enrolled_at is missing, actually ERD has created_at
        if (Schema::hasColumn('enrollments', 'enrolled_at')) {
            // Apply on enrolled_at instead
            $enrollmentQuery = DB::table('enrollments');
            $applyDateFilter($enrollmentQuery, 'enrolled_at');
        }
        if ($courseId) $enrollmentQuery->where('course_id', $courseId);

        $totalEnrollments = (clone $enrollmentQuery)->count();
        $completedEnrollments = (clone $enrollmentQuery)->where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 2) : 0;

        // Recent orders (eager load user and course)
        $latestOrdersQuery = \App\Models\Order::query()
            ->with(['user:id,full_name', 'course:id,title,slug'])
            ->orderBy('id', 'desc')
            ->limit(5);
        if ($courseId) $latestOrdersQuery->where('course_id', $courseId);
        $latestOrders = $latestOrdersQuery->get();

        // Recent courses (eager load instructor and map to instructor_name)
        $latestCoursesQuery = \App\Models\Course::query()
            ->with(['instructor:id,full_name'])
            ->whereNull('deleted_at')
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
        $pendingCourseReviews = DB::table('courses')->whereNull('deleted_at')->where('status', 'pending_review')->count();

        $pendingInstructorUpgrades = DB::table('payout_accounts')
            ->join('users', 'payout_accounts.user_id', '=', 'users.id')
            ->where('payout_accounts.status', 'pending_verification')
            ->where('users.role', 'learner')
            ->whereNull('payout_accounts.deleted_at')
            ->count();

        $pendingPayoutAccounts = DB::table('payout_accounts')
            ->join('users', 'payout_accounts.user_id', '=', 'users.id')
            ->where('payout_accounts.status', 'pending_verification')
            ->where('users.role', 'instructor')
            ->whereNull('payout_accounts.deleted_at')
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
                'total_revenue' => (float)$totalRevenue,
                'total_enrollments' => $totalEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => (float)$completionRate,
            ],
            'revenue' => [
                'gross_amount' => (float)$grossAmount,
                'instructor_amount' => (float)$instructorAmount,
                'platform_fee_amount' => (float)$platformFeeAmount,
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
                'pending_amount' => (float)$pendingWithdrawAmount,
                'approved_amount' => (float)$approvedWithdrawAmount,
                'paid_amount' => (float)$paidWithdrawAmount,
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

        $query = DB::table('orders')
            ->join('courses', 'orders.course_id', '=', 'courses.id')
            ->leftJoin('revenues', 'orders.id', '=', 'revenues.order_id')
            ->where('orders.status', 'paid')
            ->where('orders.payment_status', 'paid');

        $dateColumn = 'orders.paid_at';

        // Apply filters
        if ($dateFrom) $query->whereDate($dateColumn, '>=', $dateFrom);
        if ($dateTo) $query->whereDate($dateColumn, '<=', $dateTo);
        if ($month) $query->whereMonth($dateColumn, $month);
        if ($year) $query->whereYear($dateColumn, $year);
        if ($courseId) $query->where('orders.course_id', $courseId);
        if ($instructorId) $query->where('courses.instructor_id', $instructorId);

        // Calculate summary
        $summaryQuery = clone $query;
        $summary = [
            'total_gross_amount' => (float) $summaryQuery->sum('orders.amount'),
            'total_instructor_amount' => (float) $summaryQuery->sum(DB::raw('COALESCE(revenues.instructor_amount, orders.amount * 0.85)')),
            'total_platform_fee_amount' => (float) $summaryQuery->sum(DB::raw('COALESCE(revenues.platform_fee_amount, orders.amount * 0.15)')),
            'order_count' => $summaryQuery->count('orders.id'),
            'course_count' => $summaryQuery->distinct()->count('orders.course_id'),
            'instructor_count' => $summaryQuery->distinct()->count('courses.instructor_id'),
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

        $query->selectRaw('SUM(orders.amount) as gross_amount')
            ->selectRaw('SUM(COALESCE(revenues.instructor_amount, orders.amount * 0.85)) as instructor_amount')
            ->selectRaw('SUM(COALESCE(revenues.platform_fee_amount, orders.amount * 0.15)) as platform_fee_amount')
            ->selectRaw('COUNT(orders.id) as order_count')
            ->selectRaw('COUNT(DISTINCT orders.course_id) as course_count')
            ->selectRaw('COUNT(DISTINCT courses.instructor_id) as instructor_count');

        $query->groupBy(DB::raw($dateFormat));

        // Sorting
        $sortFieldMap = [
            'date' => 'period',
            'gross_amount' => 'gross_amount',
            'instructor_amount' => 'instructor_amount',
            'platform_fee_amount' => 'platform_fee_amount',
            'order_count' => 'order_count',
        ];

        $orderCol = $sortFieldMap[$sortBy] ?? 'period';
        $query->orderBy($orderCol, $sortDirection);

        $perPage = $filters['per_page'] ?? 15;
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
            ->whereNull('deleted_at')
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

        // Revenue
        $hasRevenues = Schema::hasTable('revenues');
        $totalRevenue = 0;
        $instructorRevenue = 0;
        $platformFee = 0;

        if ($hasRevenues) {
            $revenuesQuery = DB::table('revenues')->where('course_id', $courseId);
            $applyDateFilter($revenuesQuery, 'earned_at');
            $totalRevenue = (clone $revenuesQuery)->sum('gross_amount');
            $instructorRevenue = (clone $revenuesQuery)->sum('instructor_amount');
            $platformFee = (clone $revenuesQuery)->sum('platform_fee_amount');
        } else {
            // Fallback to orders
            $totalRevenue = (clone $paidOrdersQuery)->sum('amount');
        }

        // Enrollments
        $enrollmentsQuery = DB::table('enrollments')->where('course_id', $courseId);
        if (Schema::hasColumn('enrollments', 'enrolled_at')) {
            $applyDateFilter($enrollmentsQuery, 'enrolled_at');
        } else {
            $applyDateFilter($enrollmentsQuery, 'created_at');
        }

        $totalEnrollments = (clone $enrollmentsQuery)->count();
        $activeEnrollments = (clone $enrollmentsQuery)->where('status', 'active')->count();
        $completedEnrollments = (clone $enrollmentsQuery)->where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 2) : 0;
        $latestEnrollmentAccessedAt = (clone $enrollmentsQuery)->max('last_accessed_at');

        // Lessons
        $totalLessons = 0;
        if (Schema::hasTable('lessons')) {
            $totalLessons = DB::table('lessons')->where('course_id', $courseId)->count();
        }

        // Lesson Progress
        $completedLessonProgress = 0;
        $latestLessonAccessedAt = null;
        if (Schema::hasTable('lesson_progress') && Schema::hasTable('lessons')) {
            $lessonProgressQuery = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->where('lessons.course_id', $courseId);

            // Note: date filters usually apply to activity here, we use last_accessed_at
            $applyDateFilter($lessonProgressQuery, 'lesson_progress.last_accessed_at');

            $completedLessonProgress = (clone $lessonProgressQuery)->where('lesson_progress.status', 'completed')->count();
            $latestLessonAccessedAt = (clone $lessonProgressQuery)->max('lesson_progress.last_accessed_at');
        }

        // Quizzes
        $quizAttemptCount = 0;
        if (Schema::hasTable('quiz_attempts') && Schema::hasTable('quizzes')) {
            $quizAttemptsQuery = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->where('quizzes.course_id', $courseId);

            $applyDateFilter($quizAttemptsQuery, 'quiz_attempts.started_at');
            $quizAttemptCount = (clone $quizAttemptsQuery)->count();
        }

        // Latest activity overall
        $activities = array_filter([$latestOrderPaidAt, $latestEnrollmentAccessedAt, $latestLessonAccessedAt]);
        $latestActivityAt = empty($activities) ? null : max($activities);

        return [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug ?? null,
                'status' => $course->status ?? null,
            ],
            'summary' => [
                'total_orders' => $totalOrders,
                'paid_orders' => $paidOrders,
                'total_revenue' => (float)$totalRevenue,
                'instructor_revenue' => (float)$instructorRevenue,
                'platform_fee' => (float)$platformFee,
                'total_enrollments' => $totalEnrollments,
                'active_enrollments' => $activeEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => (float)$completionRate,
                'total_lessons' => $totalLessons,
                'completed_lesson_progress' => $completedLessonProgress,
                'quiz_attempt_count' => $quizAttemptCount,
                'latest_activity_at' => $latestActivityAt,
            ],
            'revenue' => [
                'gross_amount' => (float)$totalRevenue,
                'instructor_amount' => (float)$instructorRevenue,
                'platform_fee_amount' => (float)$platformFee,
            ],
            'enrollment' => [
                'total' => $totalEnrollments,
                'active' => $activeEnrollments,
                'completed' => $completedEnrollments,
                'completion_rate' => (float)$completionRate,
            ],
            'activity' => [
                'latest_order_paid_at' => $latestOrderPaidAt,
                'latest_enrollment_accessed_at' => $latestEnrollmentAccessedAt,
                'latest_lesson_accessed_at' => $latestLessonAccessedAt,
            ]
        ];
    }
}
