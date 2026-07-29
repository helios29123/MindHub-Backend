<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompletionStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course' => $this->resource['course'] ?? null,
            'enrollment' => $this->resource['enrollment'] ?? null,
            'lessons' => $this->resource['lessons'] ?? null,
            'quizzes' => $this->resource['quizzes'] ?? null,
            'completion_status' => $this->resource['completion_status'] ?? null,
        ];
    }
}