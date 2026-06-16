<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenueReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->period,
            'gross_amount' => (float) $this->gross_amount,
            'instructor_amount' => (float) $this->instructor_amount,
            'platform_fee_amount' => (float) $this->platform_fee_amount,
            'order_count' => (int) $this->order_count,
            'course_count' => (int) $this->course_count,
            'instructor_count' => (int) $this->instructor_count,
        ];
    }
}
