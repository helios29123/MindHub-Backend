<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearnerRiskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'learner_id' => $this->resource['learner_id'],
            'full_name' => $this->resource['full_name'],
            'email' => $this->resource['email'],
            'phone' => $this->resource['phone'],
            'enrollment_id' => $this->resource['enrollment_id'],
            'enrollment_status' => $this->resource['enrollment_status'],
            'progress_percent' => $this->resource['progress_percent'],
            'last_accessed_at' => $this->resource['last_accessed_at'],
            'risk_level' => $this->resource['risk_level'],
            'risk_score' => $this->resource['risk_score'],
            'reasons' => $this->resource['reasons'],
        ];
    }
}
