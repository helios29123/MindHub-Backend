<?php

namespace App\Repositories\Admin;

use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

final class AdminRevenueRepository
{
    public function query(array $filters)
    {
        $query = Revenue::query()
            ->with(['course', 'instructor', 'order', 'commissionRule'])
            ->latest('earned_at');

        foreach (['instructor_id', 'course_id', 'commission_rule_id'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('earned_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('earned_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    public function paginate(array $filters)
    {
        return $this->query($filters)->paginate($filters['per_page'] ?? 15);
    }

    public function summary(array $filters): array
    {
        $query = $this->query($filters);
        $revenueIds = (clone $query)->pluck('revenues.id');
        $instructorAmount = (float) (clone $query)->sum('instructor_amount');
        $allocatedAmount = $revenueIds->isEmpty()
            ? 0.0
            : (float) DB::table('withdrawal_revenues')
                ->whereIn('revenue_id', $revenueIds)
                ->sum('allocated_amount');

        return [
            'gross_amount' => (float) (clone $query)->sum('gross_amount'),
            'instructor_amount' => $instructorAmount,
            'platform_fee_amount' => (float) (clone $query)->sum('platform_fee_amount'),
            'allocated_amount' => $allocatedAmount,
            'available_amount' => max($instructorAmount - $allocatedAmount, 0),
        ];
    }

    public function sourceBreakdown(array $filters = [])
    {
        $query = Revenue::query()
            ->join('commission_rules', 'commission_rules.id', '=', 'revenues.commission_rule_id')
            ->selectRaw('revenues.commission_rule_id')
            ->selectRaw('commission_rules.name as commission_rule_name')
            ->selectRaw('commission_rules.instructor_rate')
            ->selectRaw('commission_rules.platform_rate')
            ->selectRaw('SUM(revenues.gross_amount) as gross_amount')
            ->selectRaw('SUM(revenues.instructor_amount) as instructor_amount')
            ->selectRaw('SUM(revenues.platform_fee_amount) as platform_fee_amount')
            ->selectRaw('COUNT(*) as total');

        if (! empty($filters['date_from'])) {
            $query->whereDate('revenues.earned_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('revenues.earned_at', '<=', $filters['date_to']);
        }

        return $query
            ->groupBy(
                'revenues.commission_rule_id',
                'commission_rules.name',
                'commission_rules.instructor_rate',
                'commission_rules.platform_rate'
            )
            ->orderByDesc('gross_amount')
            ->get();
    }

    public function chart(array $filters = [])
    {
        $query = Revenue::query()
            ->selectRaw('DATE_FORMAT(earned_at, "%Y-%m") as month')
            ->selectRaw('SUM(instructor_amount) as instructor_amount')
            ->selectRaw('SUM(platform_fee_amount) as platform_fee_amount')
            ->selectRaw('SUM(gross_amount) as gross_amount')
            ->selectRaw('COUNT(*) as total');

        if (! empty($filters['date_from'])) {
            $query->whereDate('earned_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('earned_at', '<=', $filters['date_to']);
        }

        return $query
            ->groupByRaw('DATE_FORMAT(earned_at, "%Y-%m")')
            ->orderBy('month')
            ->get();
    }
}
