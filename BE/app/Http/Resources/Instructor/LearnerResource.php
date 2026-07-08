<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class LearnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $progressPercent = (float) ($this->progress_percent ?? 0);
        return [
            'enrollment_id' => (int) $this->enrollment_id,
            'learner' => [
                'id' => (int) $this->learner_id,
                'full_name' => $this->learner_full_name,
                'email' => $this->learner_email,
            ],
            'course' => [
                'id' => (int) $this->course_id,
                'title' => $this->course_title,
            ],
            'status' => $this->enrollment_status,
            'status_label' => $this->enrollmentStatusLabel($this->enrollment_status),
            'progress_percent' => $progressPercent,
            'progress_label' => rtrim(rtrim(number_format($progressPercent, 2, '.', ''), '0'), '.') . '%',
            'enrolled_at' => $this->enrolled_at,
            'completed_at' => $this->completed_at,
            'last_accessed_at' => $this->last_accessed_at,
            'last_accessed_label' => $this->last_accessed_at,
        ];
    }
    private function enrollmentStatusLabel(?string $status): string
    {
        return match ($status) {
            'active' => 'Đang học',
            'completed' => 'Đã hoàn thành',
            default => 'Không xác định',
        };
    }
}