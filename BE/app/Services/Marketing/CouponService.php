<?php

namespace App\Services\Marketing;

use App\Exceptions\BusinessException;
use App\Models\Coupon;
use App\Models\Course;
use App\Repositories\Marketing\MarketingCouponRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponService
{
    public function __construct(
        private readonly MarketingCouponRepository $couponRepository,
        private readonly CouponPricingService $pricing
    ) {
    }

    public function paginateForInstructor(int $instructorId, array $filters): LengthAwarePaginator
    {
        if (!empty($filters['course_id'])) {
            $this->ensureCourseOwnedByInstructor((int) $filters['course_id'], $instructorId);
        }

        $paginator = $this->couponRepository->paginateForInstructor($instructorId, $filters);

        foreach ($paginator->items() as $coupon) {
            if ($coupon->course) {
                $this->refreshDerivedStatus($coupon);
                $this->pricing->syncCourseSalePrice($coupon->course);
            }
        }

        return $paginator;
    }

    public function summaryForInstructor(int $instructorId, array $filters = []): array
    {
        $query = $this->couponRepository->ownedQuery($instructorId);
        if (!empty($filters['course_id'])) {
            $this->ensureCourseOwnedByInstructor((int) $filters['course_id'], $instructorId);
            $query->where('course_id', (int) $filters['course_id']);
        }

        $coupons = $query->get();
        foreach ($coupons as $coupon) {
            $this->refreshDerivedStatus($coupon);
        }

        $coupons = $query->get();

        return [
            'total_coupons' => $coupons->count(),
            'scheduled_coupons' => $coupons->where('status', Coupon::STATUS_SCHEDULED)->count(),
            'active_coupons' => $coupons->where('status', Coupon::STATUS_ACTIVE)->count(),
            'inactive_coupons' => $coupons->where('status', Coupon::STATUS_INACTIVE)->count(),
            'expired_coupons' => $coupons->where('status', Coupon::STATUS_EXPIRED)->count(),
            'used_up_coupons' => $coupons->where('status', Coupon::STATUS_USED_UP)->count(),
            'total_usage_count' => (int) $coupons->sum('used_count'),
        ];
    }

    public function courseOptionsForInstructor(int $instructorId, array $filters = []): Collection
    {
        $query = Course::query()->where('instructor_id', $instructorId);
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . trim((string) $filters['search']) . '%');
        }

        return $query->orderBy('title')->get(['id', 'title', 'status', 'price', 'sale_price']);
    }

    public function getForInstructor(int $instructorId, int $couponId): Coupon
    {
        $coupon = $this->getCouponOwnedByInstructor($couponId, $instructorId);
        $this->refreshDerivedStatus($coupon);
        if ($coupon->course) {
            $this->pricing->syncCourseSalePrice($coupon->course);
        }
        return $coupon->refresh()->load('course');
    }

    public function createForInstructor(int $instructorId, array $data): Coupon
    {
        return DB::transaction(function () use ($instructorId, $data): Coupon {
            $course = Course::query()
                ->whereKey((int) $data['course_id'])
                ->where('instructor_id', $instructorId)
                ->lockForUpdate()
                ->first();

            if (!$course) {
                throw new BusinessException('Không tìm thấy khóa học hoặc bạn không có quyền thao tác.', 404);
            }

            $campaignType = (string) ($data['campaign_type'] ?? '');
            $payload = $this->normalizePayload($data, null, $campaignType);
            $payload['course_id'] = (int) $course->id;

            $this->pricing->validateCampaign($course, $payload);
            $this->assertDateRange($payload['start_at'] ?? null, $payload['end_at'] ?? null);
            $this->assertNoOverlap($course->id, $payload['start_at'] ?? null, $payload['end_at'] ?? null);

            if ($campaignType === Coupon::CAMPAIGN_TRIAL) {
                $this->assertTrialMonthlyQuota($instructorId);
            }

            $payload['status'] = $this->initialStatus($payload['start_at'] ?? null, $payload['end_at'] ?? null);
            $payload['code'] = $this->generateCode($course, $payload);
            $payload['used_count'] = 0;

            $coupon = $this->couponRepository->create($payload);
            $this->pricing->syncCourseSalePrice($course->refresh());

            return $coupon->refresh()->load('course');
        });
    }

    public function updateForInstructor(int $instructorId, int $couponId, array $data): Coupon
    {
        return DB::transaction(function () use ($instructorId, $couponId, $data): Coupon {
            $coupon = Coupon::query()->whereKey($couponId)->lockForUpdate()->first();
            if (!$coupon) {
                throw new BusinessException('Không tìm thấy campaign.', 404);
            }

            $course = Course::query()
                ->whereKey((int) $coupon->course_id)
                ->where('instructor_id', $instructorId)
                ->lockForUpdate()
                ->first();

            if (!$course) {
                throw new BusinessException('Không tìm thấy campaign hoặc bạn không có quyền thao tác.', 404);
            }

            $this->refreshDerivedStatus($coupon);
            $coupon->refresh();

            if ($coupon->isTerminal()) {
                throw new BusinessException('Campaign đã kết thúc/đã tắt, không thể mở lại hoặc chỉnh sửa. Hãy tạo campaign mới.', 409);
            }

            if (array_key_exists('course_id', $data) && (int) $data['course_id'] !== (int) $coupon->course_id) {
                throw new BusinessException('Không được chuyển campaign sang khóa học khác.', 422);
            }

            if (array_key_exists('campaign_type', $data) && $data['campaign_type'] !== $coupon->campaign_type) {
                throw new BusinessException('Không được đổi chế độ của campaign đã tạo. Hãy tạo campaign mới.', 422);
            }

            if (array_key_exists('usage_limit', $data)
                && $data['usage_limit'] !== null
                && (int) $data['usage_limit'] < (int) $coupon->used_count
            ) {
                throw new BusinessException('Giới hạn lượt dùng không được nhỏ hơn số lượt đã dùng.', 422);
            }

            if (array_key_exists('status', $data)) {
                $requested = (string) $data['status'];
                if ($requested !== Coupon::STATUS_INACTIVE) {
                    throw new BusinessException('Instructor chỉ được tắt campaign. Trạng thái active/scheduled/expired/used_up do Backend xác định.', 422);
                }

                $coupon = $this->couponRepository->update($coupon, ['status' => Coupon::STATUS_INACTIVE]);
                $this->pricing->syncCourseSalePrice($course->refresh());
                return $coupon;
            }

            $payload = $this->normalizePayload($data, $coupon, $coupon->campaign_type);
            $this->pricing->validateCampaign($course, $payload, $coupon);
            $this->assertDateRange($payload['start_at'] ?? null, $payload['end_at'] ?? null);
            $this->assertNoOverlap(
                $course->id,
                $payload['start_at'] ?? null,
                $payload['end_at'] ?? null,
                $coupon->id
            );

            $payload['status'] = $this->initialStatus($payload['start_at'] ?? null, $payload['end_at'] ?? null);

            $coupon = $this->couponRepository->update($coupon, $payload);
            $this->pricing->syncCourseSalePrice($course->refresh());

            return $coupon;
        });
    }

    public function deleteForInstructor(int $instructorId, int $couponId): Coupon
    {
        return DB::transaction(function () use ($instructorId, $couponId): Coupon {
            $coupon = Coupon::query()->whereKey($couponId)->lockForUpdate()->first();
            if (!$coupon) {
                throw new BusinessException('Không tìm thấy campaign.', 404);
            }

            $course = Course::query()
                ->whereKey((int) $coupon->course_id)
                ->where('instructor_id', $instructorId)
                ->lockForUpdate()
                ->first();

            if (!$course) {
                throw new BusinessException('Không tìm thấy campaign hoặc bạn không có quyền thao tác.', 404);
            }

            if (!$coupon->isTerminal()) {
                $coupon->forceFill(['status' => Coupon::STATUS_INACTIVE])->save();
            }

            $this->pricing->syncCourseSalePrice($course->refresh());

            return $coupon->refresh()->load('course');
        });
    }

    private function getCouponOwnedByInstructor(int $couponId, int $instructorId): Coupon
    {
        $coupon = $this->couponRepository->findOwned($couponId, $instructorId);
        if (!$coupon) {
            throw new BusinessException('Không tìm thấy campaign hoặc bạn không có quyền thao tác.', 404);
        }
        return $coupon;
    }

    private function ensureCourseOwnedByInstructor(int $courseId, int $instructorId): Course
    {
        $course = $this->couponRepository->findCourseById($courseId);
        if (!$course || (int) $course->instructor_id !== $instructorId) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }
        return $course;
    }

    private function normalizePayload(array $data, ?Coupon $existing, string $campaignType): array
    {
        $payload = [];

        foreach (['campaign_type', 'discount_type', 'discount_value', 'max_discount_amount', 'usage_limit', 'start_at', 'end_at'] as $key) {
            if (array_key_exists($key, $data)) {
                $payload[$key] = $data[$key];
            } elseif ($existing !== null) {
                $payload[$key] = $existing->{$key};
            }
        }

        $payload['campaign_type'] = $campaignType;

        if ($campaignType === Coupon::CAMPAIGN_TRIAL) {
            $payload['discount_type'] = null;
            $payload['discount_value'] = null;
            $payload['max_discount_amount'] = null;
        } elseif (($payload['discount_type'] ?? null) === Coupon::TYPE_FIXED) {
            $payload['max_discount_amount'] = null;
        }

        return $payload;
    }

    private function initialStatus(mixed $startAt, mixed $endAt): string
    {
        $now = now();

        if ($endAt !== null && Carbon::parse($endAt)->lt($now)) {
            return Coupon::STATUS_EXPIRED;
        }
        if ($startAt !== null && Carbon::parse($startAt)->gt($now)) {
            return Coupon::STATUS_SCHEDULED;
        }

        return Coupon::STATUS_ACTIVE;
    }

    private function refreshDerivedStatus(Coupon $coupon): void
    {
        if ($coupon->status === Coupon::STATUS_INACTIVE) {
            return;
        }

        $next = $this->pricing->effectiveStatus($coupon);
        if ($next !== $coupon->status) {
            $coupon->forceFill(['status' => $next])->save();
        }
    }

    private function assertDateRange(mixed $startAt, mixed $endAt): void
    {
        if ($startAt !== null && $endAt !== null && Carbon::parse($endAt)->lte(Carbon::parse($startAt))) {
            throw new BusinessException('Thời gian kết thúc phải sau thời gian bắt đầu.', 422);
        }
    }

    private function assertNoOverlap(int $courseId, mixed $startAt, mixed $endAt, ?int $excludeId = null): void
    {
        $newStart = $startAt !== null ? Carbon::parse($startAt) : null;
        $newEnd = $endAt !== null ? Carbon::parse($endAt) : null;

        $query = Coupon::query()
            ->where('course_id', $courseId)
            ->whereIn('status', [Coupon::STATUS_SCHEDULED, Coupon::STATUS_ACTIVE])
            ->lockForUpdate();

        if ($excludeId !== null) {
            $query->whereKeyNot($excludeId);
        }

        foreach ($query->get() as $existing) {
            $existingStart = $existing->start_at ? Carbon::parse($existing->start_at) : null;
            $existingEnd = $existing->end_at ? Carbon::parse($existing->end_at) : null;

            $startsBeforeOtherEnds = $newStart === null || $existingEnd === null || $newStart->lte($existingEnd);
            $otherStartsBeforeNewEnds = $existingStart === null || $newEnd === null || $existingStart->lte($newEnd);

            if ($startsBeforeOtherEnds && $otherStartsBeforeNewEnds) {
                throw new BusinessException('Thời gian campaign bị trùng với campaign khác của khóa học.', 409);
            }
        }
    }

    private function assertTrialMonthlyQuota(int $instructorId): void
    {
        $start = now()->copy()->startOfMonth();
        $end = now()->copy()->endOfMonth();

        $count = Coupon::query()
            ->where('campaign_type', Coupon::CAMPAIGN_TRIAL)
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('course', fn (Builder $q) => $q->where('instructor_id', $instructorId))
            ->lockForUpdate()
            ->count();

        if ($count >= (int) config('coupon.trial_campaigns_per_month', 2)) {
            throw new BusinessException('Bạn đã đạt giới hạn 2 campaign học thử mới trong tháng này.', 422);
        }
    }

    private function generateCode(Course $course, array $payload): string
    {
        $coursePart = Str::upper(Str::slug((string) $course->title, ''));
        $coursePart = substr($coursePart !== '' ? $coursePart : 'COURSE', 0, 12);
        $date = now()->format('dmy');

        if ($payload['campaign_type'] === Coupon::CAMPAIGN_TRIAL) {
            $offer = 'FREE';
        } elseif (($payload['discount_type'] ?? null) === Coupon::TYPE_PERCENT) {
            $offer = 'P' . (int) round((float) $payload['discount_value']);
        } else {
            $value = (int) round((float) $payload['discount_value']);
            $offer = $value >= 1000 && $value % 1000 === 0
                ? 'F' . ((int) ($value / 1000)) . 'K'
                : 'F' . $value;
        }

        for ($i = 0; $i < 20; $i++) {
            $candidate = "MH-{$coursePart}-{$offer}-{$date}-" . Str::upper(Str::random(4));
            if (!Coupon::query()->where('code', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new BusinessException('Không thể sinh mã campaign duy nhất, vui lòng thử lại.', 500);
    }
}
