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
        $rate = $total > 0 ? round(($completed / $total) * 100, 2) : 0;
        return [
            'course_id' => $this->id,
            'title' => $this->title,
            'total_enrollments' => $total,
            'completed_enrollments' => $completed,
            'completion_rate_percent' => $rate,
            'avg_progress_percent' => (float) round($this->avg_progress ?? 0, 2),
        ];
    }
}