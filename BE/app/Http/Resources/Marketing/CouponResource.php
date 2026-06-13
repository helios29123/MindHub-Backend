<?php
namespace App\Http\Resources\Marketing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'coupon_id' => $this->id,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'max_order_amount' => $this->max_order_amount,
            'usage_limit' => $this->usage_limit,
            'used_count' => $this->used_count,
            'start_at' => $this->start_at?->format('Y-m-d H:i:s'),
            'end_at' => $this->end_at?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'course' => $this->whenLoaded('course', function (): array {
                return [
                    'id' => $this->course?->id,
                    'title' => $this->course?->title,
                    'status' => $this->course?->status,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}