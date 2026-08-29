<?php

namespace App\Repositories\Marketing;

use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class InstructorCouponRepository
{
    public function summary(int $instructorId, array $filters = []): array
    {
        $query = $this->baseOwnedQuery($instructorId);
        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }

        $all = $query->get();

        return [
            'total_coupons' => $all->count(),
            'scheduled_coupons' => $all->where('status', Coupon::STATUS_SCHEDULED)->count(),
            'active_coupons' => $all->where('status', Coupon::STATUS_ACTIVE)->count(),
            'inactive_coupons' => $all->where('status', Coupon::STATUS_INACTIVE)->count(),
            'expired_coupons' => $all->where('status', Coupon::STATUS_EXPIRED)->count(),
            'used_up_coupons' => $all->where('status', Coupon::STATUS_USED_UP)->count(),
            'total_usage_count' => (int) $all->sum('used_count'),
        ];
    }

    public function paginateCoupons(int $instructorId, array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);
        $query = $this->baseOwnedQuery($instructorId)
            ->whereIn('status', [Coupon::STATUS_SCHEDULED, Coupon::STATUS_ACTIVE]);

        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('code', 'like', '%' . $search . '%');
        }
        if (!empty($filters['status']) && in_array($filters['status'], [Coupon::STATUS_SCHEDULED, Coupon::STATUS_ACTIVE], true)) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('start_at')->orderByDesc('id')->paginate($perPage);
    }

    public function findOwnedCoupon(int $couponId, int $instructorId): ?Coupon
    {
        return $this->baseOwnedQuery($instructorId)->whereKey($couponId)->first();
    }

    public function courseOwnedByInstructor(int $courseId, int $instructorId): ?Course
    {
        return Course::query()
            ->whereKey($courseId)
            ->where('instructor_id', $instructorId)
            ->first();
    }

    public function courseOptions(int $instructorId, array $filters = []): Collection
    {
        $query = Course::query()->where('instructor_id', $instructorId);
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . trim((string) $filters['search']) . '%');
        }

        return $query->orderBy('title')->get(['id', 'title', 'status', 'price', 'sale_price']);
    }

    private function baseOwnedQuery(int $instructorId): Builder
    {
        return Coupon::query()
            ->with(['course:id,title,slug,status,price,sale_price,instructor_id'])
            ->whereHas('course', fn (Builder $query) => $query->where('instructor_id', $instructorId));
    }
}
