<?php
namespace App\Http\Resources\Interaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorQuestionLessonOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) data_get($this->resource, 'id'),
            'title' => data_get($this->resource, 'title'),
            'course' => [
                'id' => (int) data_get($this->resource, 'course_id'),
                'title' => data_get($this->resource, 'course_title'),
            ],
            'section' => [
                'id' => data_get($this->resource, 'section_id') !== null ? (int) data_get($this->resource, 'section_id') : null,
                'title' => data_get($this->resource, 'section_title'),
            ],
        ];
    }
}