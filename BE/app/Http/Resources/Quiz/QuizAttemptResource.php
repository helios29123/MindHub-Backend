<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isSubmitted = $this->status === 'submitted' || $this->submitted_at !== null;

        $answersData = [];
        if ($this->relationLoaded('answers')) {
            $answersData = $this->answers->map(function ($answer) use ($isSubmitted) {
                $question = $answer->question;
                $data = [
                    'question_id' => $answer->question_id,
                    'question_type' => $question ? $question->question_type : null,
                    'selected_option_id' => $answer->option_id,
                ];

                if ($question && isset($question->question_text)) {
                    $data['question_text'] = $question->question_text;
                }

                if ($isSubmitted) {
                    $data['is_correct'] = (bool) $answer->is_correct;
                    $data['score_earned'] = (float) $answer->score_earned;
                    
                    if ($question && $question->relationLoaded('options') && count($question->options) > 0) {
                        $correctOption = $question->options->firstWhere('is_correct', true);
                        if ($correctOption) {
                            $data['correct_option_id'] = $correctOption->id;
                        }
                    }
                }

                if ($question && $question->relationLoaded('options') && count($question->options) > 0) {
                    $data['options'] = $question->options->map(function ($opt) use ($isSubmitted) {
                        $optData = [
                            'id' => $opt->id,
                            'option_text' => $opt->option_text ?? null,
                        ];
                        if ($isSubmitted) {
                            $optData['is_correct'] = (bool) $opt->is_correct;
                        }
                        return $optData;
                    });
                }

                return $data;
            });
        }

        return [
            'attempt_id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'quiz_title' => $this->relationLoaded('quiz') && $this->quiz ? $this->quiz->title : null,
            'attempt_number' => $this->attempt_number,
            'score' => (float) $this->score,
            'total_score' => (float) $this->total_score,
            'passed' => (bool) $this->passed,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'submitted_at' => $this->submitted_at,
            'answers' => $answersData,
        ];
    }
}
