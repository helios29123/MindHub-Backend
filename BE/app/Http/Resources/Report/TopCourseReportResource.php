<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopCourseReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enrollmentCount = (int) ($this->enrollment_count ?? 0);
        $completedCount = (int) ($this->completed_count ?? 0);
        $completionRate = $enrollmentCount > 0 ? round(($completedCount / $enrollmentCount) * 100, 2) : 0;

        return [
            'course_id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'instructor' => $this->whenLoaded('instructor', function () {
                return [
                    'id' => $this->instructor->id,
                    'full_name' => $this->instructor->full_name,
                    'email' => $this->instructor->email,
                    'role' => $this->instructor->role,
                    'status' => $this->instructor->status,
                ];
            }),
            'sold_count' => (int) ($this->sold_count ?? 0),
            'enrollment_count' => $enrollmentCount,
            'completed_count' => $completedCount,
            'completion_rate' => $completionRate,
            'total_revenue' => (float) ($this->total_revenue ?? 0),
            'last_paid_at' => $this->last_paid_at ? \Carbon\Carbon::parse($this->last_paid_at)->toIso8601String() : null,
        ];
    }
}
