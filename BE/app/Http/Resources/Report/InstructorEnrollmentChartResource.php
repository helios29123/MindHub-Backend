<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorEnrollmentChartResource extends JsonResource
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
            'enrollment_count' => $this->resource['enrollment_count'],
            'value' => (int) ($this->resource['enrollment_count'] ?? 0),
            'completed_count' => $this->resource['completed_count'],
        ];
    }
}