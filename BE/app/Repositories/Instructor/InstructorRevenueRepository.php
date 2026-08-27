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
        $statusBreakdown = $this->getStatusBreakdown(clone $baseQuery);

        $hasSaleSource = \Illuminate\Support\Facades\Schema::hasColumn('revenues', 'sale_source');

        $selectFields = '
            revenues.course_id,
            courses.title as course_title,
            DATE_FORMAT(revenues.earned_at, "%Y-%m") as revenue_month,
            COUNT(revenues.id) as revenue_count,
            COALESCE(SUM(revenues.gross_amount), 0) as gross_amount,
            COALESCE(SUM(revenues.instructor_amount), 0) as instructor_amount,
            COALESCE(SUM(revenues.platform_fee_amount), 0) as platform_fee_amount,
            MIN(revenues.earned_at) as first_earned_at,
            MAX(revenues.earned_at) as last_earned_at
        ';

        $groupByFields = [
            'revenues.course_id',
            'courses.title',
            DB::raw('DATE_FORMAT(revenues.earned_at, "%Y-%m")'),
        ];

        if ($hasSaleSource) {
            $selectFields .= ',
                COALESCE(revenues.sale_source, "marketplace_default") as sale_source,
                COALESCE(revenues.commission_rule_code, "marketplace_default") as commission_rule_code,
                COALESCE(revenues.instructor_percent, 70.00) as instructor_percent,
                COALESCE(revenues.platform_percent, 30.00) as platform_percent
            ';
            $groupByFields = array_merge($groupByFields, [
                'revenues.sale_source',
                'revenues.commission_rule_code',
                'revenues.instructor_percent',
                'revenues.platform_percent'
            ]);
        } else {
            $selectFields .= ',
                "marketplace_default" as sale_source,
                "marketplace_default" as commission_rule_code,
                70.00 as instructor_percent,
                30.00 as platform_percent
            ';
        }

        $groupedQuery = (clone $baseQuery)
            ->selectRaw($selectFields)
            ->groupBy($groupByFields)
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
                'by_status' => $statusBreakdown,
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
            ->whereNull('deleted_at')
            ->exists();
    }

    public function courseExists(int $courseId): bool
    {
        return DB::table('courses')
            ->where('id', $courseId)
            ->whereNull('deleted_at')
            ->exists();
    }

    private function baseRevenueQuery(int $instructorId, array $filters): Builder
    {
        $query = DB::table('revenues')
            ->join('courses', 'courses.id', '=', 'revenues.course_id')
            ->leftJoin('orders', 'orders.id', '=', 'revenues.order_id')
            ->where('revenues.instructor_id', $instructorId)
            ;

        if (!empty($filters['course_id'])) {
            $query->where('revenues.course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('revenues.earned_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('revenues.earned_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('revenues.earned_at', (int) $filters['month']);
        }

        if (!empty($filters['year'])) {
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

    private function getStatusBreakdown(Builder $query): array
    {
        return $query
            ->selectRaw('
                revenues.status,
                COUNT(revenues.id) as revenue_count,
                COALESCE(SUM(revenues.gross_amount), 0) as gross_amount,
                COALESCE(SUM(revenues.instructor_amount), 0) as instructor_amount,
                COALESCE(SUM(revenues.platform_fee_amount), 0) as platform_fee_amount
            ')
            ->groupBy('revenues.status')
            ->get()
            ->mapWithKeys(function ($row): array {
                return [
                    $row->status => [
                        'revenue_count' => (int) $row->revenue_count,
                        'gross_amount' => $this->formatMoney($row->gross_amount),
                        'instructor_amount' => $this->formatMoney($row->instructor_amount),
                        'platform_fee_amount' => $this->formatMoney($row->platform_fee_amount),
                    ],
                ];
            })
            ->all();
    }

    private function formatItems(LengthAwarePaginator $paginator): array
    {
        $labels = [
            'marketplace_default' => 'Marketplace mặc định',
            'platform_ads' => 'Quảng cáo nền tảng',
            'admin_campaign' => 'Chiến dịch admin',
            'instructor_coupon' => 'Mã giảm giá giảng viên',
            'instructor_referral' => 'Link giới thiệu giảng viên',
        ];

        return $paginator
            ->getCollection()
            ->map(function ($row) use ($labels): array {
                return [
                    'course_id' => (int) $row->course_id,
                    'course_title' => $row->course_title,
                    'month' => $row->revenue_month,
                    'revenue_count' => (int) $row->revenue_count,
                    'gross_amount' => $this->formatMoney($row->gross_amount),
                    'instructor_amount' => $this->formatMoney($row->instructor_amount),
                    'platform_fee_amount' => $this->formatMoney($row->platform_fee_amount),
                    'first_earned_at' => $row->first_earned_at,
                    'last_earned_at' => $row->last_earned_at,
                    'sale_source' => $row->sale_source,
                    'sale_source_label' => $labels[$row->sale_source] ?? 'Marketplace mặc định',
                    'commission_rule_code' => $row->commission_rule_code,
                    'instructor_percent' => (float) $row->instructor_percent,
                    'platform_percent' => (float) $row->platform_percent,
                ];
            })
            ->values()
            ->all();
    }

    private function formatMoney(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}