<?php
namespace App\Repositories\Marketing;
use App\Models\Coupon;
use App\Models\Course;
use Carbon\Carbon;
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
        $coupons = $query->get();
        $summary = [
            'total_coupons' => $coupons->count(),
            'active_coupons' => 0,
            'inactive_coupons' => 0,
            'expired_coupons' => 0,
            'used_up_coupons' => 0,
        ];
        foreach ($coupons as $coupon) {
            $status = $this->effectiveStatus($coupon);
            if ($status === Coupon::STATUS_ACTIVE) {
                $summary['active_coupons']++;
            }
            if ($status === Coupon::STATUS_INACTIVE) {
                $summary['inactive_coupons']++;
            }
            if ($status === Coupon::STATUS_EXPIRED) {
                $summary['expired_coupons']++;
            }
            if ($status === Coupon::STATUS_USED_UP) {
                $summary['used_up_coupons']++;
            }
        }
        return $summary;
    }
    public function paginateCoupons(int $instructorId, array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);
        $query = $this->baseOwnedQuery($instructorId);
        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }
        if (!empty($filters['code'])) {
            $code = trim((string) $filters['code']);
            $query->where('code', 'like', '%' . $code . '%');
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $subQuery) use ($search): void {
                $subQuery->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        $status = $filters['status'] ?? 'all';
        if ($status !== 'all' && $status !== null) {
            $this->applyEffectiveStatusFilter($query, (string) $status);
        }
        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
    public function findOwnedCoupon(int $couponId, int $instructorId): ?Coupon
    {
        return $this->baseOwnedQuery($instructorId)
            ->where('id', $couponId)
            ->first();
    }
    public function create(array $data): Coupon
    {
        $coupon = Coupon::query()->create($data);
        return $coupon->load('course');
    }
    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->fill($data);
        $coupon->save();
        return $coupon->refresh()->load('course');
    }
    public function delete(Coupon $coupon): Coupon
    {
        $coupon->delete();
        return $coupon;
    }
    public function courseOwnedByInstructor(int $courseId, int $instructorId): ?Course
    {
        return Course::query()
            ->where('id', $courseId)
            ->where('instructor_id', $instructorId)
            
            ->first();
    }
    public function courseOptions(int $instructorId, array $filters = []): Collection
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 100), 1), 100);
        $page = max((int) ($filters['page'] ?? 1), 1);
        $query = Course::query()
            ->where('instructor_id', $instructorId)
            ;
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('title', 'like', '%' . $search . '%');
        }
        return $query
            ->orderBy('title')
            ->forPage($page, $perPage)
            ->get(['id', 'title', 'status']);
    }
    private function baseOwnedQuery(int $instructorId): Builder
    {
        return Coupon::query()
            ->with(['course:id,title,slug,status,instructor_id'])
            ->where('user_id', $instructorId)
            ->whereNotNull('course_id')
            ->whereHas('course', function (Builder $query) use ($instructorId): void {
                $query->where('instructor_id', $instructorId)
                    ;
            });
    }
    private function applyEffectiveStatusFilter(Builder $query, string $status): void
    {
        $now = now();
        if ($status === Coupon::STATUS_INACTIVE) {
            $query->where('status', Coupon::STATUS_INACTIVE);
            return;
        }
        if ($status === Coupon::STATUS_EXPIRED) {
            $query->where('status', '!=', Coupon::STATUS_INACTIVE)
                ->whereNotNull('end_at')
                ->where('end_at', '<', $now);
            return;
        }
        if ($status === Coupon::STATUS_USED_UP) {
            $query->where('status', '!=', Coupon::STATUS_INACTIVE)
                ->where(function (Builder $subQuery) use ($now): void {
                    $subQuery->whereNull('end_at')
                        ->orWhere('end_at', '>=', $now);
                })
                ->whereNotNull('usage_limit')
                ->whereColumn('used_count', '>=', 'usage_limit');
            return;
        }
        if ($status === Coupon::STATUS_ACTIVE) {
            $query->where('status', Coupon::STATUS_ACTIVE)
                ->where(function (Builder $subQuery) use ($now): void {
                    $subQuery->whereNull('end_at')
                        ->orWhere('end_at', '>=', $now);
                })
                ->where(function (Builder $subQuery): void {
                    $subQuery->whereNull('usage_limit')
                        ->orWhereColumn('used_count', '<', 'usage_limit');
                });
        }
    }
    private function effectiveStatus(Coupon $coupon): string
    {
        if ($coupon->status === Coupon::STATUS_INACTIVE) {
            return Coupon::STATUS_INACTIVE;
        }
        if ($coupon->end_at !== null && Carbon::parse($coupon->end_at)->lt(now())) {
            return Coupon::STATUS_EXPIRED;
        }
        if ($coupon->usage_limit !== null && (int) $coupon->used_count >= (int) $coupon->usage_limit) {
            return Coupon::STATUS_USED_UP;
        }
        return Coupon::STATUS_ACTIVE;
    }
}