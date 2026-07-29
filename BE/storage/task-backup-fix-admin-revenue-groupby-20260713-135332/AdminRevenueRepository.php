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
    public function sourceBreakdown(array $filters): array
    {
        return $this->query($filters)->select('sale_channel', DB::raw('SUM(gross_amount) gross_amount'), DB::raw('SUM(instructor_amount) instructor_amount'), DB::raw('SUM(platform_fee_amount) platform_fee_amount'), DB::raw('COUNT(*) total'))->groupBy('sale_channel')->orderByDesc('gross_amount')->get()->toArray();
    }
    public function chart(array $filters): array
    {
        return $this->query($filters)->selectRaw('DATE_FORMAT(earned_at, "%Y-%m") month, SUM(instructor_amount) instructor_amount, SUM(platform_fee_amount) platform_fee_amount')->groupBy('month')->orderBy('month')->get()->toArray();
    }
}
