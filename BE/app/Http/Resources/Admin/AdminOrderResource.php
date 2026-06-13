<?php
namespace App\Http\Resources\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class AdminOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'order_code' => $this->order_code,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'provider_transaction_id' => $this->provider_transaction_id,
            'price_snapshot' => $this->price_snapshot !== null ? (string) $this->price_snapshot : null,
            'amount' => $this->amount !== null ? (string) $this->amount : null,
            'paid_at' => $this->paid_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'user' => $this->whenLoaded('user', function (): ?array {
                if (!$this->user) {
                    return null;
                }
                return [
                    'id' => (int) $this->user->id,
                    'full_name' => $this->user->full_name,
                    'email' => $this->user->email,
                    'role' => $this->user->role,
                    'status' => $this->user->status,
                ];
            }),
            'course' => $this->whenLoaded('course', function (): ?array {
                if (!$this->course) {
                    return null;
                }
                return [
                    'id' => (int) $this->course->id,
                    'title' => $this->course->title,
                    'slug' => $this->course->slug,
                    'status' => $this->course->status,
                    'price' => $this->course->price !== null ? (string) $this->course->price : null,
                    'sale_price' => $this->course->sale_price !== null ? (string) $this->course->sale_price : null,
                ];
            }),
            'coupon' => $this->whenLoaded('coupon', function (): ?array {
                if (!$this->coupon) {
                    return null;
                }
                return [
                    'id' => (int) $this->coupon->id,
                    'code' => $this->coupon->code,
                    'name' => $this->coupon->name,
                    'discount_type' => $this->coupon->discount_type,
                    'discount_value' => $this->coupon->discount_value !== null ? (string) $this->coupon->discount_value : null,
                    'status' => $this->coupon->status,
                ];
            }),
        ];
    }
}