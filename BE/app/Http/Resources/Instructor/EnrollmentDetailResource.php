<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class EnrollmentDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enrollment = $this->resource['enrollment'];
        return [
            'enrollment' => [
                'id' => (int) $enrollment->enrollment_id,
                'status' => $enrollment->enrollment_status,
                'status_label' => $this->enrollmentStatusLabel($enrollment->enrollment_status),
                'progress_percent' => (float) $enrollment->progress_percent,
                'enrolled_at' => $enrollment->enrolled_at,
                'completed_at' => $enrollment->completed_at,
                'last_accessed_at' => $enrollment->last_accessed_at,
            ],
            'learner' => [
                'id' => (int) $enrollment->learner_id,
                'full_name' => $enrollment->learner_full_name,
                'email' => $enrollment->learner_email,
            ],
            'course' => [
                'id' => (int) $enrollment->course_id,
                'title' => $enrollment->course_title,
            ],
            'lesson_progress' => $this->resource['lesson_progress'] ?? [],
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