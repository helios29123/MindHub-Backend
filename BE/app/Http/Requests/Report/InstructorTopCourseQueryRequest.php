<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class InstructorTopCourseQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'preset' => ['nullable', 'string'],
            'period' => ['nullable', 'string'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'course_id' => ['nullable', 'integer'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }
}