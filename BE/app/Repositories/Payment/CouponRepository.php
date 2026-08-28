<?php

namespace App\Repositories\Payment;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;

class CouponRepository
{
    public function findByCode(string $couponCode): ?Coupon
    {
        return Coupon::query()
            ->where('code', strtoupper(trim($couponCode)))
            ->first();
    }

    public function findCurrentForCourse(int $courseId): ?Coupon
    {
        return Coupon::query()
            ->where('course_id', $courseId)
            ->whereIn('status', [Coupon::STATUS_SCHEDULED, Coupon::STATUS_ACTIVE])
            ->orderBy('start_at')
            ->orderBy('id')
            ->get()
            ->first(fn (Coupon $coupon): bool => $coupon->isActiveNow());
    }

    public function lockCurrentForCourse(int $courseId): ?Coupon
    {
        return Coupon::query()
            ->where('course_id', $courseId)
            ->whereIn('status', [Coupon::STATUS_SCHEDULED, Coupon::STATUS_ACTIVE])
            ->where(function (Builder $q): void {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->orderBy('start_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    public function incrementUsedCount(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }
}
