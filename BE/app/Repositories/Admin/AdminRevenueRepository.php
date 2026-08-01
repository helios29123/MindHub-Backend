<?php

namespace App\Repositories\Admin;

use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

final class AdminRevenueRepository
{
    public function query(array $filters)
    {
        $q = Revenue::query()->with(['course', 'instructor', 'order'])->latest('earned_at');
        foreach (['instructor_id', 'course_id', 'status', 'sale_channel'] as $f) {
            if (!empty($filters[$f])) $q->where($f, $filters[$f]);
        }
        if (!empty($filters['date_from'])) $q->whereDate('earned_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $q->whereDate('earned_at', '<=', $filters['date_to']);
        return $q;
    }
    public function paginate(array $filters)
    {
        return $this->query($filters)->paginate($filters['per_page'] ?? 15);
    }
    public function summary(array $filters): array
    {
        $q = $this->query($filters);
        return ['gross_amount' => (float)(clone $q)->sum('gross_amount'), 'instructor_amount' => (float)(clone $q)->sum('instructor_amount'), 'platform_fee_amount' => (float)(clone $q)->sum('platform_fee_amount'), 'available_amount' => (float)(clone $q)->where('status', 'available')->sum('instructor_amount'), 'paid_amount' => (float)(clone $q)->where('status', 'paid')->sum('instructor_amount')];
    }
    public function sourceBreakdown(array $filters = [])
{
    $query = \App\Models\Revenue::query()
        ->selectRaw('COALESCE(sale_source, "unknown") as sale_channel')
        ->selectRaw('SUM(gross_amount) as gross_amount')
        ->selectRaw('SUM(instructor_amount) as instructor_amount')
        ->selectRaw('SUM(platform_fee_amount) as platform_fee_amount')
        ->selectRaw('COUNT(*) as total');

    if (!empty($filters['from_date'])) {
        $query->whereDate('earned_at', '>=', $filters['from_date']);
    }

    if (!empty($filters['to_date'])) {
        $query->whereDate('earned_at', '<=', $filters['to_date']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('earned_at', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('earned_at', '<=', $filters['date_to']);
    }

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    return $query
        ->groupByRaw('COALESCE(sale_source, "unknown")')
        ->orderByRaw('gross_amount DESC')
        ->get();
}


    public function chart(array $filters = [])
{
    $query = \App\Models\Revenue::query()
        ->selectRaw('DATE_FORMAT(earned_at, "%Y-%m") as month')
        ->selectRaw('SUM(instructor_amount) as instructor_amount')
        ->selectRaw('SUM(platform_fee_amount) as platform_fee_amount')
        ->selectRaw('SUM(gross_amount) as gross_amount')
        ->selectRaw('COUNT(*) as total');

    if (!empty($filters['from_date'])) {
        $query->whereDate('earned_at', '>=', $filters['from_date']);
    }

    if (!empty($filters['to_date'])) {
        $query->whereDate('earned_at', '<=', $filters['to_date']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('earned_at', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('earned_at', '<=', $filters['date_to']);
    }

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    return $query
        ->groupByRaw('DATE_FORMAT(earned_at, "%Y-%m")')
        ->orderBy('month', 'asc')
        ->get();
}


}
