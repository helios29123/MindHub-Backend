<?php
namespace App\Http\Resources\Marketing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorCouponSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_coupons' => (int) data_get($this->resource, 'total_coupons', 0),
            'active_coupons' => (int) data_get($this->resource, 'active_coupons', 0),
            'inactive_coupons' => (int) data_get($this->resource, 'inactive_coupons', 0),
            'expired_coupons' => (int) data_get($this->resource, 'expired_coupons', 0),
            'used_up_coupons' => (int) data_get($this->resource, 'used_up_coupons', 0),
        ];
    }
}