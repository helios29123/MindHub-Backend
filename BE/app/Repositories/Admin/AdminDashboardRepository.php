<?php

namespace App\Repositories\Admin;

use App\Models\Course;
use App\Models\Order;
use App\Models\PayoutAccount;
use App\Models\WithdrawRequest;
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

final class AdminDashboardRepository
{
    public function kpis(): array
    {
        return [
            'total_revenue_month' => (float)Order::query()->where('status', 'paid')->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('amount'),
            'instructor_revenue_month' => (float)Revenue::query()->whereMonth('earned_at', now()->month)->whereYear('earned_at', now()->year)->sum('instructor_amount'),
            'platform_revenue_month' => (float)Revenue::query()->whereMonth('earned_at', now()->month)->whereYear('earned_at', now()->year)->sum('platform_fee_amount'),
            'paid_orders' => Order::query()->where('status', 'paid')->count(),
            'pending_payout_items' => WithdrawRequest::query()->whereIn('status', [WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_APPROVED, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_MANUAL_REQUIRED])->count(),
            'action_required' => Course::query()->where('status', 'pending_review')->count() + Order::query()->where('status', Order::STATUS_PENDING_PAYMENT)->count() + PayoutAccount::query()->where('status', 'pending_verification')->count(),
        ];
    }
    public function revenueChart(): array
    {
        return Revenue::query()->selectRaw('DATE_FORMAT(earned_at, "%Y-%m") as month, SUM(instructor_amount) instructor_amount, SUM(platform_fee_amount) platform_fee_amount')->groupBy('month')->orderBy('month')->limit(12)->get()->toArray();
    }
    public function sourceBreakdown(): array
    {
        return Revenue::query()->select('sale_channel', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(gross_amount) as gross_amount'), DB::raw('SUM(instructor_amount) as instructor_amount'), DB::raw('SUM(platform_fee_amount) as platform_fee_amount'))->groupBy('sale_channel')->get()->toArray();
    }
}
