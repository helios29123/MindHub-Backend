<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseAnalyticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course' => [
                'id' => $this->resource['course']['id'],
                'title' => $this->resource['course']['title'],
                'slug' => $this->resource['course']['slug'],
                'status' => $this->resource['course']['status'],
            ],
            'learning' => [
                'enrollment_count' => $this->resource['learning']['enrollment_count'],
                'completed_enrollment_count' => $this->resource['learning']['completed_enrollment_count'],
                'completion_rate' => $this->resource['learning']['completion_rate'],
                'average_progress' => $this->resource['learning']['average_progress'],
            ],

            'revenue' => [
                'instructor_amount' => $this->resource['revenue']['instructor_amount'],
            ],
            'review' => [
                'average_rating' => $this->resource['review']['average_rating'],
                'review_count' => $this->resource['review']['review_count'],
            ],
        ];
    }
}
