<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InstructorProfileSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = data_get($this->resource, 'profile');
        $completion = data_get($this->resource, 'completion', []);

        return [
            'bio' => data_get($profile, 'bio'),
            'expertise' => data_get($profile, 'expertise'),
            'experience_years' => data_get($profile, 'experience_years'),
            'level' => data_get($profile, 'level'),
            'completion' => [
                'completed_fields' => (int) data_get($completion, 'completed_fields', 0),
                'total_fields' => (int) data_get($completion, 'total_fields', 4),
                'is_completed' => (bool) data_get($completion, 'is_completed', false),
                'missing_fields' => data_get($completion, 'missing_field_names', []),
            ],
        ];
    }
}