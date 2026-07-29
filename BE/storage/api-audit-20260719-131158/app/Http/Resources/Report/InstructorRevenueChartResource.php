<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorRevenueChartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->resource['period'],
            'gross_amount' => $this->resource['gross_amount'],
            'instructor_amount' => $this->resource['instructor_amount'],
            'platform_fee_amount' => $this->resource['platform_fee_amount'],
        ];
    }
}