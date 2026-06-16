<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseLearnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'learner_id' => $this->learner_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'learner_status' => $this->learner_status,
            'enrollment_id' => $this->enrollment_id,
            'enrollment_status' => $this->enrollment_status,
            'last_accessed_at' => $this->last_accessed_at,
            'completed_at' => $this->completed_at,
            'enrolled_at' => $this->enrolled_at ?? null,
            'progress_percent' => isset($this->progress_percent) ? (float) $this->progress_percent : null,
        ];
    }
}
