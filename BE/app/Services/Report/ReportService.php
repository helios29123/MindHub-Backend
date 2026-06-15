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
}
