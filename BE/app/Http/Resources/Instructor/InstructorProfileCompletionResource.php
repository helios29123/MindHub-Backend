<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InstructorProfileCompletionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'completed_fields' => (int) data_get($this->resource, 'completed_fields', 0),
            'total_fields' => (int) data_get($this->resource, 'total_fields', 4),
            'is_completed' => (bool) data_get($this->resource, 'is_completed', false),
            'missing_fields' => data_get($this->resource, 'missing_fields', []),
        ];
    }
}