<?php
namespace App\Http\Resources\Marketing;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class InstructorCouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $effectiveStatus = $this->effectiveStatus();
        $usageLimit = $this->usage_limit !== null ? (int) $this->usage_limit : null;
        $usedCount = (int) ($this->used_count ?? 0);
        $remainingUsage = $usageLimit === null ? null : max(0, $usageLimit - $usedCount);
        return [
            'id' => (int) $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'course' => [
                'id' => $this->course ? (int) $this->course->id : null,
                'title' => $this->course?->title,
                'slug' => $this->course?->slug,
                'status' => $this->course?->status,
            ],
            'discount_type' => $this->discount_type,
            'discount_type_label' => $this->discountTypeLabel($this->discount_type),
            'discount_value' => $this->moneyValue($this->discount_value),
            'max_order_amount' => $this->moneyValue($this->max_order_amount),
            'usage_limit' => $usageLimit,
            'used_count' => $usedCount,
            'remaining_usage' => $remainingUsage,
            'usage_label' => $usageLimit === null ? $usedCount . '/Không giới hạn lượt' : $usedCount . '/' . $usageLimit . ' lượt',
            'start_at' => $this->formatDate($this->start_at),
            'end_at' => $this->formatDate($this->end_at),
            'status' => $this->status,
            'status_label' => $this->statusLabel($this->status),
            'effective_status' => $effectiveStatus,
            'effective_status_label' => $this->statusLabel($effectiveStatus),
            'computed_status' => $effectiveStatus,
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at),
        ];
    }
    protected function effectiveStatus(): string
    {
        if ($this->status === Coupon::STATUS_INACTIVE) {
            return Coupon::STATUS_INACTIVE;
        }
        if ($this->end_at !== null && Carbon::parse($this->end_at)->lt(now())) {
            return Coupon::STATUS_EXPIRED;
        }
        if ($this->usage_limit !== null && (int) $this->used_count >= (int) $this->usage_limit) {
            return Coupon::STATUS_USED_UP;
        }
        return Coupon::STATUS_ACTIVE;
    }
    protected function discountTypeLabel(?string $type): string
    {
        return match ($type) {
            Coupon::TYPE_PERCENT => 'Giảm theo phần trăm',
            Coupon::TYPE_FIXED => 'Giảm số tiền cố định',
            default => 'Không xác định',
        };
    }
    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            Coupon::STATUS_ACTIVE => 'Đang hoạt động',
            Coupon::STATUS_INACTIVE => 'Đã tắt',
            Coupon::STATUS_EXPIRED => 'Hết hạn',
            Coupon::STATUS_USED_UP => 'Hết lượt dùng',
            'deleted' => 'Đã xóa',
            default => 'Không xác định',
        };
    }
    protected function moneyValue(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        return (float) $value;
    }
    protected function formatDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}