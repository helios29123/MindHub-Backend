<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'order_code' => $this->order_code,
            'course_id' => $this->course_id,
            'coupon_id' => $this->coupon_id,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'price_snapshot' => $this->price_snapshot,
            'amount' => $this->amount,
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),

            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course?->id,
                    'title' => $this->course?->title,
                    'slug' => $this->course?->slug,
                    'thumbnail_url' => $this->course?->thumbnail_url,
                ];
            }),

            'coupon' => $this->whenLoaded('coupon', function () {
                return $this->coupon ? [
                    'id' => $this->coupon->id,
                    'code' => $this->coupon->code,
                    'name' => $this->coupon->name,
                ] : null;
            }),

            'enrollment' => $this->whenLoaded('enrollment', function () {
                return $this->enrollment ? [
                    'id' => $this->enrollment->id,
                    'status' => $this->enrollment->status,
                    'enrolled_at' => $this->enrollment->enrolled_at?->format('Y-m-d H:i:s'),
                ] : null;
            }),
        ];
    }
}