<?php

namespace App\Services\Report;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getTopCoursesReport(array $filters)
    {
        $query = Course::query()
            ->select('courses.id', 'courses.title', 'courses.slug', 'courses.status', 'courses.price', 'courses.sale_price', 'courses.instructor_id')
            ->with(['instructor:id,full_name,email,role,status']);

        if (!empty($filters['course_id'])) {
            $query->where('courses.id', $filters['course_id']);
        }

        // Base query for orders
        $orderQuery = DB::table('orders')
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

        // Base query for enrollments
        $enrollmentQuery = DB::table('enrollments')
            ->select('course_id')
            ->selectRaw('COUNT(id) as enrollment_count')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->groupBy('course_id');

        $query->leftJoinSub($orderQuery, 'o', function ($join) {
            $join->on('courses.id', '=', 'o.course_id');
        });

        $query->leftJoinSub($enrollmentQuery, 'e', function ($join) {
            $join->on('courses.id', '=', 'e.course_id');
        });

        $query->addSelect([
            DB::raw('COALESCE(o.sold_count, 0) as sold_count'),
            DB::raw('COALESCE(o.total_revenue, 0) as total_revenue'),
            'o.last_paid_at',
            DB::raw('COALESCE(e.enrollment_count, 0) as enrollment_count'),
            DB::raw('COALESCE(e.completed_count, 0) as completed_count'),
        ]);

        $sortBy = $filters['sort_by'] ?? 'sold_count';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if ($sortBy === 'completion_rate') {
            $query->orderByRaw('CASE WHEN COALESCE(e.enrollment_count, 0) > 0 THEN (COALESCE(e.completed_count, 0) * 100.0 / e.enrollment_count) ELSE 0 END ' . $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        // Also secondary sort by course ID to keep deterministic
        $query->orderBy('courses.id', 'desc');

        $perPage = $filters['per_page'] ?? 15;
        $paginator = $query->paginate($perPage);

        // Calculate summary
        // Summary might need to be total of the filtered results, not just the page
        // But the query is paginated. So let's aggregate the paginator. Or we do a separate query.
        $summary = [
            'total_courses' => $paginator->total(),
            'total_sold' => 0,
            'total_revenue' => 0,
            'total_completed' => 0,
        ];
        
        // To avoid an expensive secondary query, we could just calculate total sum in a single query without pagination
        // Or we can just calculate the sums of the current page, or full total?
        // Usually report summaries expect full total. Let's do a fast sum query on the base query without pagination.
        $summaryQuery = clone $query;
        // remove orders and selects for the summary
        $summaryQuery->getQuery()->orders = null;
        $summaryQuery->getQuery()->groups = null;
        
        // Since we already left joined, we can just SUM on the subqueries
        $totals = DB::query()->fromSub($summaryQuery, 't')
            ->selectRaw('SUM(sold_count) as total_sold')
            ->selectRaw('SUM(total_revenue) as total_revenue')
            ->selectRaw('SUM(completed_count) as total_completed')
            ->first();

        if ($totals) {
            $summary['total_sold'] = (int) $totals->total_sold;
            $summary['total_revenue'] = (float) $totals->total_revenue;
            $summary['total_completed'] = (int) $totals->total_completed;
        }

        return [
            'paginator' => $paginator,
            'summary' => $summary,
        ];
    }

    public function getTopInstructorsReport(array $filters)
    {
        $query = \App\Models\User::query()
            ->select('users.id', 'users.full_name', 'users.email', 'users.role', 'users.status')
            ->where('users.role', 'instructor');

        if (!empty($filters['course_id'])) {
            $query->whereHas('courses', function($q) use ($filters) {
                $q->where('id', $filters['course_id']);
            });
        }

        $courseQuery = DB::table('courses')
            ->select('instructor_id')
            ->selectRaw('COUNT(id) as total_courses')
            ->selectRaw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_courses")
            ->whereNull('deleted_at')
            ->groupBy('instructor_id');

        if (!empty($filters['course_id'])) {
            $courseQuery->where('id', $filters['course_id']);
        }

        $revenueQuery = DB::table('orders')
            ->join('courses', 'orders.course_id', '=', 'courses.id')
            ->select('courses.instructor_id')
            ->selectRaw('COUNT(orders.id) as total_sold')
            ->selectRaw('SUM(orders.amount) as total_revenue')
            // Since we use orders, instructor_amount might not be available unless we have a fixed percentage, or we just set it to total_revenue for now or 0.
            ->selectRaw('0 as instructor_amount')
            ->selectRaw('0 as platform_fee_amount')
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

        $enrollmentQuery = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->select('courses.instructor_id')
            ->selectRaw('COUNT(enrollments.id) as total_enrollments')
            ->selectRaw("SUM(CASE WHEN enrollments.status = 'completed' THEN 1 ELSE 0 END) as total_completed")
            ->groupBy('courses.instructor_id');

        if (!empty($filters['course_id'])) {
            $enrollmentQuery->where('enrollments.course_id', $filters['course_id']);
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
            DB::raw('COALESCE(e.total_completed, 0) as total_completed'),
        ]);

        $sortBy = $filters['sort_by'] ?? 'total_revenue';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if ($sortBy === 'completion_rate') {
            $query->orderByRaw('CASE WHEN COALESCE(e.total_enrollments, 0) > 0 THEN (COALESCE(e.total_completed, 0) * 100.0 / e.total_enrollments) ELSE 0 END ' . $sortDirection);
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
            ->whereNull('courses.deleted_at');

        // Base condition for "bỏ dở": not completed
        $query->where(function($q) {
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
        $revenueQuery = DB::table('revenues');
        // Only valid revenues
        // The table might not exist in some states, or might be empty. Fallback to orders
        $hasRevenues = \Schema::hasTable('revenues') && DB::table('revenues')->exists();
        
        if ($hasRevenues) {
            $applyDateFilter($revenueQuery, 'earned_at');
            if ($courseId) $revenueQuery->where('course_id', $courseId);
            
            $grossAmount = (clone $revenueQuery)->sum('gross_amount');
            $instructorAmount = (clone $revenueQuery)->sum('instructor_amount');
            $platformFeeAmount = (clone $revenueQuery)->sum('platform_fee_amount');
            $totalRevenue = $grossAmount;
        } else {
            // Fallback to orders.amount
            $grossAmount = (clone $paidOrderQuery)->sum('amount');
            $instructorAmount = 0; // cannot calculate without revenues table reliably
            $platformFeeAmount = 0;
            $totalRevenue = $grossAmount;
        }

        // Enrollments
        $enrollmentQuery = DB::table('enrollments');
        $applyDateFilter($enrollmentQuery, 'created_at'); // or enrolled_at if it exists, assume created_at works if enrolled_at is missing, actually ERD has created_at
        if (\Schema::hasColumn('enrollments', 'enrolled_at')) {
            // Apply on enrolled_at instead
            $enrollmentQuery = DB::table('enrollments');
            $applyDateFilter($enrollmentQuery, 'enrolled_at');
        }
        if ($courseId) $enrollmentQuery->where('course_id', $courseId);

        $totalEnrollments = (clone $enrollmentQuery)->count();
        $completedEnrollments = (clone $enrollmentQuery)->where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 2) : 0;

        // Recent
        $latestOrdersQuery = DB::table('orders')->orderBy('id', 'desc')->limit(5);
        if ($courseId) $latestOrdersQuery->where('course_id', $courseId);
        $latestOrders = $latestOrdersQuery->get();

        $latestCoursesQuery = DB::table('courses')->whereNull('deleted_at')->orderBy('id', 'desc')->limit(5);
        if ($courseId) $latestCoursesQuery->where('id', $courseId);
        $latestCourses = $latestCoursesQuery->get();

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
            'recent' => [
                'latest_orders' => $latestOrders,
                'latest_courses' => $latestCourses,
            ]
        ];
    }
}
