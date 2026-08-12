<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorQuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'course_id' => (int) $this->course_id,
            'lesson_id' => $this->lesson_id ? (int) $this->lesson_id : null,
            'title' => $this->title,
            'description' => $this->description,
            'passing_score' => number_format((float) $this->passing_score, 2, '.', ''),
            'status' => $this->status,
            'lesson' => $this->whenLoaded('lesson', function (): ?array {
                if (!$this->lesson) {
                    return null;
                }

                return [
                    'id' => (int) $this->lesson->id,
                    'title' => $this->lesson->title,
                    'status' => $this->lesson->status,
                ];
            }),
            'course' => $this->whenLoaded('course', function (): ?array {
                if (!$this->course) {
                    return null;
                }

                return [
                    'id' => (int) $this->course->id,
                    'title' => $this->course->title,
                    'status' => $this->course->status,
                    'instructor_id' => (int) $this->course->instructor_id,
                ];
            }),
            'questions' => $this->whenLoaded('questions', function (): array {
                return $this->questions->map(function ($question): array {
                    return [
                        'id' => (int) $question->id,
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'score' => number_format((float) $question->score, 2, '.', ''),
                        'sort_order' => (int) $question->sort_order,
                        'explanation' => $question->explanation,
                        'options' => $question->relationLoaded('options')
                            ? $question->options->map(function ($option): array {
                                return [
                                    'id' => (int) $option->id,
                                    'option_text' => $option->option_text,
                                    'is_correct' => (bool) $option->is_correct,
                                    'sort_order' => (int) $option->sort_order,
                                ];
                            })->values()->all()
                            : [],
                    ];
                })->values()->all();
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}