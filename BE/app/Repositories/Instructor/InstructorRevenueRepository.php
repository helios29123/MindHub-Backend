<?php

namespace App\Repositories\Instructor;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class InstructorRevenueRepository
{
    public function getRevenueReport(int $instructorId, array $filters): array
    {
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 100);
        $baseQuery = $this->baseRevenueQuery($instructorId, $filters);
        $summary = $this->getSummary(clone $baseQuery);

        $groupedQuery = (clone $baseQuery)
            ->join('commission_rules', 'commission_rules.id', '=', 'revenues.commission_rule_id')
            ->selectRaw('
                revenues.course_id,
                courses.title as course_title,
                revenues.commission_rule_id,
                commission_rules.name as commission_rule_name,
                commission_rules.instructor_rate,
                commission_rules.platform_rate,
                DATE_FORMAT(revenues.earned_at, "%Y-%m") as revenue_month,
                COUNT(revenues.id) as revenue_count,
                COALESCE(SUM(revenues.gross_amount), 0) as gross_amount,
                COALESCE(SUM(revenues.instructor_amount), 0) as instructor_amount,
                COALESCE(SUM(revenues.platform_fee_amount), 0) as platform_fee_amount,
                MIN(revenues.earned_at) as first_earned_at,
                MAX(revenues.earned_at) as last_earned_at
            ')
            ->groupBy(
                'revenues.course_id',
                'courses.title',
                'revenues.commission_rule_id',
                'commission_rules.name',
                'commission_rules.instructor_rate',
                'commission_rules.platform_rate',
                DB::raw('DATE_FORMAT(revenues.earned_at, "%Y-%m")')
            )
            ->orderByDesc('revenue_month')
            ->orderBy('courses.title');

        $paginator = $groupedQuery->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => $this->formatItems($paginator),
            'summary' => [
                'total_revenue_count' => (int) ($summary->total_revenue_count ?? 0),
                'total_gross_amount' => $this->formatMoney($summary->total_gross_amount ?? 0),
                'total_instructor_amount' => $this->formatMoney($summary->total_instructor_amount ?? 0),
                'total_platform_fee_amount' => $this->formatMoney($summary->total_platform_fee_amount ?? 0),
            ],
            'filters' => [
                'course_id' => isset($filters['course_id']) ? (int) $filters['course_id'] : null,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
                'month' => isset($filters['month']) ? (int) $filters['month'] : null,
                'year' => isset($filters['year']) ? (int) $filters['year'] : null,
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function instructorOwnsCourse(int $instructorId, int $courseId): bool
    {
        return DB::table('courses')
            ->where('id', $courseId)
            ->where('instructor_id', $instructorId)
            ->exists();
    }

    public function courseExists(int $courseId): bool
    {
        return DB::table('courses')->where('id', $courseId)->exists();
    }

    private function baseRevenueQuery(int $instructorId, array $filters): Builder
    {
        $query = DB::table('revenues')
            ->join('courses', 'courses.id', '=', 'revenues.course_id')
            ->where('revenues.instructor_id', $instructorId);

        if (! empty($filters['course_id'])) {
            $query->where('revenues.course_id', (int) $filters['course_id']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('revenues.earned_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('revenues.earned_at', '<=', $filters['date_to']);
        }
        if (! empty($filters['month'])) {
            $query->whereMonth('revenues.earned_at', (int) $filters['month']);
        }
        if (! empty($filters['year'])) {
            $query->whereYear('revenues.earned_at', (int) $filters['year']);
        }

        return $query;
    }

    private function getSummary(Builder $query): object
    {
        return $query
            ->selectRaw('
                COUNT(revenues.id) as total_revenue_count,
                COALESCE(SUM(revenues.gross_amount), 0) as total_gross_amount,
                COALESCE(SUM(revenues.instructor_amount), 0) as total_instructor_amount,
                COALESCE(SUM(revenues.platform_fee_amount), 0) as total_platform_fee_amount
            ')
            ->first();
    }

    private function formatItems(LengthAwarePaginator $paginator): array
    {
        return $paginator->getCollection()->map(function ($row): array {
            return [
                'course_id' => (int) $row->course_id,
                'course_title' => $row->course_title,
                'month' => $row->revenue_month,
                'revenue_count' => (int) $row->revenue_count,
                'gross_amount' => $this->formatMoney($row->gross_amount),
                'instructor_amount' => $this->formatMoney($row->instructor_amount),
                'platform_fee_amount' => $this->formatMoney($row->platform_fee_amount),
                'commission_rule_id' => (int) $row->commission_rule_id,
                'commission_rule_name' => $row->commission_rule_name,
                'instructor_rate' => (float) $row->instructor_rate,
                'platform_rate' => (float) $row->platform_rate,
                'first_earned_at' => $row->first_earned_at,
                'last_earned_at' => $row->last_earned_at,
            ];
        })->values()->all();
    }

    private function formatMoney(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
