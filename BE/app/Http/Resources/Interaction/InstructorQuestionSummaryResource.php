<?php
namespace App\Http\Resources\Interaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorQuestionSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_questions' => (int) data_get($this->resource, 'total_questions', 0),
            'unanswered_questions' => (int) data_get($this->resource, 'unanswered_questions', 0),
            'answered_questions' => (int) data_get($this->resource, 'answered_questions', 0),
        ];
    }
}