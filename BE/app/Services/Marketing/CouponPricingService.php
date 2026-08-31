<?php

namespace App\Services\Marketing;

use App\Exceptions\BusinessException;
use App\Models\Coupon;
use App\Models\Course;
use Carbon\Carbon;
use Carbon\CarbonInterface;

final class CouponPricingService
{
    public function quote(Course $course, ?Coupon $coupon = null, ?CarbonInterface $at = null): array
    {
        $at ??= now();
        $price = $this->money($course->price);

        if ($coupon === null || !$this->isEffective($coupon, $at)) {
            return [
                'price' => $price,
                'discount_amount' => 0,
                'sale_price' => $price,
                'coupon_id' => null,
                'campaign_type' => null,
            ];
        }

        if ($coupon->campaign_type === Coupon::CAMPAIGN_TRIAL) {
            return [
                'price' => $price,
                'discount_amount' => $price,
                'sale_price' => 0,
                'coupon_id' => (int) $coupon->id,
                'campaign_type' => Coupon::CAMPAIGN_TRIAL,
            ];
        }

        $discount = $this->discountAmount($coupon, $price);
        $salePrice = max(0, $price - $discount);

        return [
            'price' => $price,
            'discount_amount' => $discount,
            'sale_price' => $salePrice,
            'coupon_id' => (int) $coupon->id,
            'campaign_type' => Coupon::CAMPAIGN_DISCOUNT,
        ];
    }

    public function quoteCurrentCourse(Course $course, ?CarbonInterface $at = null): array
    {
        $at ??= now();

        $coupon = Coupon::query()
            ->where('course_id', $course->id)
            ->whereIn('status', [Coupon::STATUS_SCHEDULED, Coupon::STATUS_ACTIVE])
            ->orderBy('start_at')
            ->orderBy('id')
            ->get()
            ->first(fn (Coupon $candidate): bool => $this->isEffective($candidate, $at));

        return $this->quote($course, $coupon, $at);
    }

    public function isEffective(Coupon $coupon, ?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if ($coupon->status === Coupon::STATUS_INACTIVE) {
            return false;
        }
        if ($coupon->start_at !== null && Carbon::parse($coupon->start_at)->gt($at)) {
            return false;
        }
        if ($coupon->end_at !== null && Carbon::parse($coupon->end_at)->lt($at)) {
            return false;
        }
        if ($coupon->usage_limit !== null && (int) $coupon->used_count >= (int) $coupon->usage_limit) {
            return false;
        }

        return in_array($coupon->status, [Coupon::STATUS_SCHEDULED, Coupon::STATUS_ACTIVE], true);
    }

    public function effectiveStatus(Coupon $coupon, ?CarbonInterface $at = null): string
    {
        $at ??= now();

        if ($coupon->status === Coupon::STATUS_INACTIVE) {
            return Coupon::STATUS_INACTIVE;
        }
        if ($coupon->end_at !== null && Carbon::parse($coupon->end_at)->lt($at)) {
            return Coupon::STATUS_EXPIRED;
        }
        if ($coupon->usage_limit !== null && (int) $coupon->used_count >= (int) $coupon->usage_limit) {
            return Coupon::STATUS_USED_UP;
        }
        if ($coupon->start_at !== null && Carbon::parse($coupon->start_at)->gt($at)) {
            return Coupon::STATUS_SCHEDULED;
        }

        return Coupon::STATUS_ACTIVE;
    }

    public function validateCampaign(Course $course, array $data, ?Coupon $existing = null): void
    {
        $campaignType = (string) ($data['campaign_type'] ?? $existing?->campaign_type ?? '');

        if ($campaignType === Coupon::CAMPAIGN_TRIAL) {
            $this->validateTrial($data, $existing);
            return;
        }

        if ($campaignType !== Coupon::CAMPAIGN_DISCOUNT) {
            throw new BusinessException('Chế độ campaign không hợp lệ.', 422, [
                'campaign_type' => ['Chỉ chấp nhận discount hoặc trial.'],
            ]);
        }

        $this->validateDiscount($course, $data, $existing);
    }

    public function validateDiscount(Course $course, array $data, ?Coupon $existing = null): void
    {
        $type = (string) ($data['discount_type'] ?? $existing?->discount_type ?? '');
        $value = (float) ($data['discount_value'] ?? $existing?->discount_value ?? 0);
        $maxDiscount = array_key_exists('max_discount_amount', $data)
            ? $data['max_discount_amount']
            : $existing?->max_discount_amount;

        if (!in_array($type, [Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED], true)) {
            throw new BusinessException('Loại giảm giá không hợp lệ.', 422);
        }

        if ($value <= 0) {
            throw new BusinessException('Giá trị giảm phải lớn hơn 0.', 422);
        }

        $price = $this->money($course->price);
        $maxAllowed = $this->money($price * ((float) config('coupon.discount_max_percent', 70) / 100));

        if ($type === Coupon::TYPE_PERCENT) {
            if ($value > (float) config('coupon.discount_max_percent', 70)) {
                throw new BusinessException('Phần trăm giảm vượt giới hạn cho phép.', 422, [
                    'discount_value' => ['Bạn chỉ có thể giảm tối đa 70%.'],
                    'maximum_discount_amount' => [$maxAllowed],
                ]);
            }
            if ($maxDiscount !== null && (float) $maxDiscount > $maxAllowed) {
                throw new BusinessException('Mức giảm tối đa vượt giới hạn cho phép.', 422, [
                    'max_discount_amount' => ["Bạn chỉ có thể giảm tối đa {$maxAllowed}đ cho khóa học này."],
                ]);
            }
        } else {
            if ($maxDiscount !== null) {
                throw new BusinessException('Coupon giảm tiền cố định không sử dụng max_discount_amount.', 422, [
                    'max_discount_amount' => ['Trường này phải để trống với coupon fixed.'],
                ]);
            }
            if ($value > $maxAllowed) {
                throw new BusinessException('Mức giảm tiền vượt giới hạn cho phép.', 422, [
                    'discount_value' => ["Bạn chỉ có thể giảm tối đa {$maxAllowed}đ cho khóa học này."],
                    'maximum_discount_amount' => [$maxAllowed],
                ]);
            }
        }

        $temp = new Coupon([
            'campaign_type' => Coupon::CAMPAIGN_DISCOUNT,
            'discount_type' => $type,
            'discount_value' => $value,
            'max_discount_amount' => $maxDiscount,
            'status' => Coupon::STATUS_ACTIVE,
        ]);

        $discount = $this->discountAmount($temp, $price);
        $sale = max(0, $price - $discount);
        $minimum = (int) config('order.minimum_payable_amount', 10000);

        if ($sale < $minimum) {
            $maxByMinimum = max(0, $price - $minimum);
            throw new BusinessException('Giá sau giảm thấp hơn mức tối thiểu.', 422, [
                'discount_value' => ["Giá sau giảm phải tối thiểu {$minimum}đ. Với khóa học này, bạn chỉ có thể giảm tối đa {$maxByMinimum}đ."],
                'maximum_discount_amount' => [$maxByMinimum],
            ]);
        }
    }

    public function validateTrial(array $data, ?Coupon $existing = null): void
    {
        $discountType = array_key_exists('discount_type', $data) ? $data['discount_type'] : $existing?->discount_type;
        $discountValue = array_key_exists('discount_value', $data) ? $data['discount_value'] : $existing?->discount_value;
        $maxDiscount = array_key_exists('max_discount_amount', $data) ? $data['max_discount_amount'] : $existing?->max_discount_amount;

        if ($discountType !== null || $discountValue !== null || $maxDiscount !== null) {
            throw new BusinessException('Trial không sử dụng discount_type, discount_value hoặc max_discount_amount.', 422);
        }

        $usageLimit = array_key_exists('usage_limit', $data) ? $data['usage_limit'] : $existing?->usage_limit;
        if ($usageLimit === null || (int) $usageLimit < 1 || (int) $usageLimit > (int) config('coupon.trial_max_uses', 15)) {
            throw new BusinessException('Số lượt học thử không hợp lệ.', 422, [
                'usage_limit' => ['Học thử cho phép từ 1 đến 15 lượt.'],
            ]);
        }

        $startAt = array_key_exists('start_at', $data) ? $data['start_at'] : $existing?->start_at;
        $endAt = array_key_exists('end_at', $data) ? $data['end_at'] : $existing?->end_at;

        if ($startAt === null || $endAt === null) {
            throw new BusinessException('Trial bắt buộc có thời gian bắt đầu và kết thúc.', 422);
        }

        $start = Carbon::parse($startAt);
        $end = Carbon::parse($endAt);
        if ($end->lte($start)) {
            throw new BusinessException('Thời gian kết thúc phải sau thời gian bắt đầu.', 422);
        }

        $seconds = $start->diffInSeconds($end);
        $maxSeconds = (int) config('coupon.trial_campaign_max_days', 3) * 86400;
        if ($seconds > $maxSeconds) {
            throw new BusinessException('Campaign học thử không được vượt quá 3 ngày.', 422);
        }
    }

    public function discountAmount(Coupon $coupon, int $price): int
    {
        if ($coupon->campaign_type !== Coupon::CAMPAIGN_DISCOUNT) {
            return $price;
        }

        if ($coupon->discount_type === Coupon::TYPE_PERCENT) {
            $amount = $this->money($price * ((float) $coupon->discount_value / 100));
            if ($coupon->max_discount_amount !== null) {
                $amount = min($amount, $this->money($coupon->max_discount_amount));
            }
            return min($amount, $price);
        }

        return min($this->money($coupon->discount_value), $price);
    }

    public function syncCourseSalePrice(Course $course): int
    {
        $quote = $this->quoteCurrentCourse($course);
        $salePrice = (int) $quote['sale_price'];

        if ((int) round((float) $course->sale_price) !== $salePrice) {
            $course->forceFill(['sale_price' => $salePrice])->saveQuietly();
        }

        return $salePrice;
    }

    private function money(mixed $value): int
    {
        return (int) round((float) $value);
    }
}
