<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class LessonProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'lesson_id' => (int) $this->lesson_id,
            'title' => $this->lesson_title,
            'lesson_type' => $this->lesson_type,
            'status' => $this->progress_status,
            'status_label' => $this->lessonStatusLabel($this->progress_status),
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'last_accessed_at' => $this->last_accessed_at,
            'learning_duration_seconds' => (int) ($this->learning_duration_seconds ?? 0),
            'video' => $this->lesson_type === 'video'
                ? [
                    'current_second' => (int) ($this->current_second ?? 0),
                    'duration_seconds' => $this->video_duration_seconds !== null
                        ? (int) $this->video_duration_seconds
                        : null,
                ]
                : null,
        ];
    }
    private function lessonStatusLabel(?string $status): string
    {
        return match ($status) {
            'not_started' => 'Chưa học',
            'in_progress' => 'Đang học',
            'completed' => 'Đã học',
            default => 'Không xác định',
        };
    }
}