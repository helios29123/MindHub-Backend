<?php

namespace App\Http\Resources\Interaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'comment_id' => (int) $this->id,
            'content' => $this->content,
            'status' => $this->question_status,
            'is_answered' => $this->question_status === 'answered',
            'status_label' => $this->question_status === 'answered' ? 'Đã trả lời' : 'Chưa trả lời',
            'created_at' => $this->created_at,
            'learner' => [
                'id' => (int) $this->learner_id,
                'full_name' => $this->learner_name,
                'email' => $this->learner_email,
                'avatar_url' => property_exists($this->resource, 'learner_avatar_url') && $this->learner_avatar_url ? (str_starts_with($this->learner_avatar_url, 'http') ? $this->learner_avatar_url : url($this->learner_avatar_url)) : null,
            ],
            'course' => [
                'id' => (int) $this->course_id,
                'title' => $this->course_title,
            ],
            'lesson' => [
                'id' => (int) $this->lesson_id,
                'title' => $this->lesson_title,
            ],
            'reply_count' => (int) $this->reply_count,
            'instructor_reply_count' => (int) $this->instructor_reply_count,
            'answer_count' => (int) $this->instructor_reply_count,

        ];
    }
}
