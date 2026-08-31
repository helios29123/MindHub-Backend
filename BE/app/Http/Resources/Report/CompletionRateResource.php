<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompletionRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $total = (int) ($this->total_enrollments ?? 0);
        $completed = (int) ($this->completed_enrollments ?? 0);
        $started = (int) ($this->started_enrollments ?? 0);

        // Completion rate formula: completed / started learning * 100
        $rate = $started > 0 ? round(($completed / $started) * 100, 2) : 0.0;

        return [
            'course_id' => (int) $this->id,
            'title' => $this->title,
            'total_enrollments' => $total,
            'started_enrollments' => $started,
            'completed_enrollments' => $completed,
            'completion_rate_percent' => (float) $rate,
            'avg_progress_percent' => (float) round((float) ($this->avg_progress ?? 0), 2),
        ];
    }
}