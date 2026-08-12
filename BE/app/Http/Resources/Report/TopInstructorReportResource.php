<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopInstructorReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enrollments = (int) ($this->total_enrollments ?? 0);
        $completed = (int) ($this->total_completed ?? 0);
        $completionRate = $enrollments > 0 ? round(($completed / $enrollments) * 100, 2) : 0;

        return [
            'instructor_id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'total_courses' => (int) ($this->total_courses ?? 0),
            'published_courses' => (int) ($this->published_courses ?? 0),
            'total_sold' => (int) ($this->total_sold ?? 0),
            'sales_count' => (int) ($this->total_sold ?? 0),
            'total_enrollments' => $enrollments,
            'total_completed' => $completed,
            'completion_rate' => $completionRate,
            'total_revenue' => (float) ($this->total_revenue ?? 0),
            'gross_revenue' => (float) ($this->total_revenue ?? 0),
            'instructor_amount' => (float) ($this->instructor_amount ?? 0),
            'platform_fee_amount' => (float) ($this->platform_fee_amount ?? 0),
            'last_activity_at' => $this->last_activity_at ? \Carbon\Carbon::parse($this->last_activity_at)->toIso8601String() : null,
        ];
    }
}
