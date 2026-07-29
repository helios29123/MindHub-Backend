<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class InstructorLearnerIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'status' => ['nullable', 'in:active,completed'],
            'search' => ['nullable', 'string', 'max:255'],
            'enrolled_from' => ['nullable', 'date'],
            'enrolled_to' => ['nullable', 'date', 'after_or_equal:enrolled_from'],
            'sort' => ['nullable', 'in:newest,oldest,progress_asc,progress_desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}