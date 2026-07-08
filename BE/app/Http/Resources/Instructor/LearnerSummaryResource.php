<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class LearnerSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_enrollments' => (int) ($this->resource['total_enrollments'] ?? 0),
            'active_enrollments' => (int) ($this->resource['active_enrollments'] ?? 0),
            'completed_enrollments' => (int) ($this->resource['completed_enrollments'] ?? 0),
        ];
    }
}