<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorRevenueChartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $period = $this->resource['period'] ?? $this->resource['date'] ?? '';
        $name = $period;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            $name = date('d/m', strtotime($period));
        }

        return [
            'period' => $period,
            'date' => $period,
            'name' => $name,
            'gross_amount' => $this->resource['gross_amount'],
            'instructor_amount' => $this->resource['instructor_amount'],
            'value' => (float) ($this->resource['instructor_amount'] ?? 0),
            'platform_fee_amount' => $this->resource['platform_fee_amount'],
        ];
    }
}