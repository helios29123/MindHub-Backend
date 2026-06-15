<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InactiveLearnerReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'learner_id' => $this->learner_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'course_id' => $this->course_id,
            'course_title' => $this->course_title,
            'course_slug' => $this->course_slug,
            'enrollment_id' => $this->enrollment_id,
            'enrollment_status' => $this->enrollment_status,
            'last_activity_at' => $this->last_activity_at ? \Carbon\Carbon::parse($this->last_activity_at)->toIso8601String() : null,
            'inactive_days' => $this->last_activity_at ? (int) \Carbon\Carbon::parse($this->last_activity_at)->diffInDays(now()) : null,
            'progress_percent' => (float) ($this->progress_percent ?? 0),
            'enrolled_at' => $this->enrolled_at ? \Carbon\Carbon::parse($this->enrolled_at)->toIso8601String() : null,
        ];
    }
}
