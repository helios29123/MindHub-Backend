<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorLearnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'enrollment_id' => (int) $this->enrollment_id,
            'learner' => [
                'id' => (int) $this->learner_id,
                'full_name' => $this->learner_name,
                'email' => $this->learner_email,
                'avatar_url' => property_exists($this->resource, 'learner_avatar_url') && $this->learner_avatar_url ? (str_starts_with($this->learner_avatar_url, 'http') ? $this->learner_avatar_url : url($this->learner_avatar_url)) : null,
                'avatar' => property_exists($this->resource, 'learner_avatar_url') && $this->learner_avatar_url ? (str_starts_with($this->learner_avatar_url, 'http') ? $this->learner_avatar_url : url($this->learner_avatar_url)) : null,
            ],
            'course' => [
                'id' => (int) $this->course_id,
                'title' => $this->course_title,
            ],
            'status' => $this->status,
            'progress_percent' => $this->progress_percent,
            'enrolled_at' => $this->enrolled_at,
            'completed_at' => $this->completed_at,
            'last_accessed_at' => $this->last_accessed_at,
        ];
    }
}