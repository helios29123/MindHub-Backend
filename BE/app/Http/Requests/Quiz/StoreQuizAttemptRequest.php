<?php

namespace App\Http\Requests\Quiz;

use App\Http\Requests\BaseApiRequest;

class StoreQuizAttemptRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer', 'exists:quiz_questions,id'],
            'answers.*.option_id' => ['required', 'integer', 'exists:quiz_options,id'],
        ];
    }
}
