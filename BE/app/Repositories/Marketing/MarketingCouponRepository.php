<?php

namespace App\Repositories\Marketing;

use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MarketingCouponRepository
{
    public function paginateForInstructor(int $instructorId, array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 100);

        $query = $this->ownedQuery($instructorId)
            ->whereIn('status', [Coupon::STATUS_SCHEDULED, Coupon::STATUS_ACTIVE]);

        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }
        if (!empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
            $query->where('code', 'like', $search);
        }
        if (!empty($filters['status']) && in_array($filters['status'], [Coupon::STATUS_SCHEDULED, Coupon::STATUS_ACTIVE], true)) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('start_at')->orderByDesc('id')->paginate($perPage);
    }

    public function ownedQuery(int $instructorId): Builder
    {
        return Coupon::query()
            ->with('course:id,title,slug,price,sale_price,instructor_id,status')
            ->whereHas('course', fn (Builder $q) => $q->where('instructor_id', $instructorId));
    }

    public function findById(int $id): ?Coupon
    {
        return Coupon::query()
            ->with('course:id,title,slug,price,sale_price,instructor_id,status')
            ->find($id);
    }

    public function findOwned(int $id, int $instructorId): ?Coupon
    {
        return $this->ownedQuery($instructorId)->whereKey($id)->first();
    }

    public function findCourseById(int $courseId): ?Course
    {
        return Course::query()->find($courseId);
    }

    public function create(array $data): Coupon
    {
        return Coupon::query()->create($data)->load('course');
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->fill($data)->save();
        return $coupon->refresh()->load('course');
    }
}
