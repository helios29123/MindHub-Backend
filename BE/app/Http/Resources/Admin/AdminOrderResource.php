<?php
namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isCanonicalPaid = $this->status === 'paid' && $this->payment_status === 'paid';
        $paidHasEnrollment = $isCanonicalPaid ? ($this->enrollment !== null) : true;
        $paidHasRevenue = $isCanonicalPaid ? ($this->revenue !== null) : true;
        $amountsMatch = $isCanonicalPaid && $this->revenue ? ((float) $this->revenue->gross_amount === (float) $this->amount) : true;

        return [
            'id' => (int) $this->id,
            'order_code' => $this->order_code,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'provider_transaction_id' => $this->provider_transaction_id,
            'price_snapshot' => $this->price_snapshot !== null ? (string) $this->price_snapshot : null,
            'discount_amount' => $this->discount_amount !== null ? (string) $this->discount_amount : '0.00',
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
            'enrollment' => $this->relationLoaded('enrollment') && $this->enrollment ? [
                'id' => (int) $this->enrollment->id,
                'progress_percent' => (string) $this->enrollment->progress_percent,
                'status' => $this->enrollment->status,
            ] : null,
            'revenue' => $this->relationLoaded('revenue') && $this->revenue ? [
                'id' => (int) $this->revenue->id,
                'gross_amount' => (string) $this->revenue->gross_amount,
                'instructor_amount' => (string) $this->revenue->instructor_amount,
                'platform_amount' => (string) $this->revenue->platform_fee_amount,
            ] : null,
            'consistency' => [
                'paid_has_enrollment' => $paidHasEnrollment,
                'paid_has_revenue' => $paidHasRevenue,
                'amounts_match' => $amountsMatch,
            ]
        ];
    }
}