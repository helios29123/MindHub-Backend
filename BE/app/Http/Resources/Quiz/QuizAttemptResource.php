<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'attempt_id' => $this->id,
            'score' => (float) $this->score,
            'total_score' => (float) $this->total_score,
            'passed' => (bool) $this->passed,
        ];
    }
}
