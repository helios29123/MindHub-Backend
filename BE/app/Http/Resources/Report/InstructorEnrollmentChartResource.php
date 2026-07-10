<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorEnrollmentChartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->resource['period'],
            'enrollment_count' => $this->resource['enrollment_count'],
            'completed_count' => $this->resource['completed_count'],
        ];
    }
}