<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InstructorCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'title' => data_get($this->resource, 'title'),
            'slug' => data_get($this->resource, 'slug'),
            'thumbnail_url' => data_get($this->resource, 'thumbnail_url'),
            'price' => data_get($this->resource, 'price'),
            'sale_price' => data_get($this->resource, 'sale_price'),
            'status' => data_get($this->resource, 'status'),
            'published_at' => data_get($this->resource, 'published_at'),
            'admin_reject_reason' => data_get($this->resource, 'admin_reject_reason'),

            'category_name' => data_get($this->resource, 'category_name'),
            'level' => data_get($this->resource, 'level'),
            'duration_minutes' => data_get($this->resource, 'duration_minutes'),

            'enrollment_count' => (int) data_get($this->resource, 'enrollment_count', 0),
            'learner_count' => (int) data_get($this->resource, 'learner_count', 0),
            'lesson_count' => (int) data_get($this->resource, 'lesson_count', 0),
            'section_count' => (int) data_get($this->resource, 'section_count', 0),

            'gross_revenue' => data_get($this->resource, 'gross_revenue'),
            'instructor_revenue' => data_get($this->resource, 'instructor_revenue'),

            'created_at' => data_get($this->resource, 'created_at'),
            'updated_at' => data_get($this->resource, 'updated_at'),
        ];
    }
}