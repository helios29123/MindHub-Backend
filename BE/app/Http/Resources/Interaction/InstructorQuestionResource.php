<?php
namespace App\Http\Resources\Interaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAnswered = (bool) data_get($this->resource, 'is_answered', false);
        return [
            'comment_id' => (int) data_get($this->resource, 'comment_id'),
            'content' => data_get($this->resource, 'content'),
            'created_at' => data_get($this->resource, 'created_at'),
            'learner' => [
                'id' => (int) data_get($this->resource, 'learner_id'),
                'full_name' => data_get($this->resource, 'learner_full_name'),
                'email' => data_get($this->resource, 'learner_email'),
            ],
            'lesson' => [
                'id' => (int) data_get($this->resource, 'lesson_id'),
                'title' => data_get($this->resource, 'lesson_title'),
            ],
            'course' => [
                'id' => (int) data_get($this->resource, 'course_id'),
                'title' => data_get($this->resource, 'course_title'),
            ],
            'is_answered' => $isAnswered,
            'status_label' => $isAnswered ? 'Đã trả lời' : 'Chưa trả lời',
            'reply_count' => (int) data_get($this->resource, 'reply_count', 0),
        ];
    }
}