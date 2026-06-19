<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseChecklistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : [];

        return [
            'course_id' => (int) ($data['course_id'] ?? 0),
            'course_title' => $data['course_title'] ?? null,
            'course_status' => $data['course_status'] ?? null,
            'strict' => (bool) ($data['strict'] ?? false),
            'passed' => (bool) ($data['passed'] ?? false),
            'missing_items' => array_values($data['missing_items'] ?? []),
            'warnings' => array_values($data['warnings'] ?? []),
            'summary' => $data['summary'] ?? [],
            'checks' => array_values($data['checks'] ?? []),
        ];
    }
}