<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class AdminRevenueResource extends JsonResource
{
    public function toArray($request): array
    {
        $gross = (float)$this->gross_amount;
        $instructorAmt = (float)$this->instructor_amount;
        $platformAmt = (float)$this->platform_fee_amount;
        
        $instructorRate = $gross > 0 ? round(($instructorAmt / $gross) * 100) : 70;
        $platformRate = $gross > 0 ? round(($platformAmt / $gross) * 100) : 30;

        $amountConsistent = abs(($instructorAmt + $platformAmt) - $gross) < 0.01;

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order' => $this->order ? [
                'id' => $this->order->id,
                'order_code' => $this->order->order_code,
                'payment_status' => $this->order->payment_status,
                'payment_method' => $this->order->payment_method,
                'amount' => (float)$this->order->amount,
            ] : null,
            'course' => $this->course ? [
                'id' => $this->course->id,
                'title' => $this->course->title,
                'thumbnail_url' => $this->course->thumbnail_url,
                'course_level' => $this->course->course_level,
            ] : null,
            'instructor' => $this->instructor ? [
                'id' => $this->instructor->id,
                'name' => $this->instructor->full_name ?? $this->instructor->name,
                'full_name' => $this->instructor->full_name ?? $this->instructor->name,
                'email' => $this->instructor->email,
            ] : null,
            'gross_amount' => $gross,
            'instructor_amount' => $instructorAmt,
            'platform_fee_amount' => $platformAmt,
            'instructor_rate' => $instructorRate,
            'platform_rate' => $platformRate,
            'earned_at' => $this->earned_at ? \Carbon\Carbon::parse($this->earned_at)->toIso8601String() : null,
            'amount_consistent' => $amountConsistent,
        ];
    }
}
