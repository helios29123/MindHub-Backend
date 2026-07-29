<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class CourseChecklistRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $data = [
            'course_id' => $this->route('courseId'),
        ];

        if ($this->has('strict')) {
            $booleanValue = filter_var(
                $this->query('strict'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($booleanValue !== null) {
                $data['strict'] = $booleanValue;
            }
        }

        $this->merge($data);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'min:1'],
            'strict' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Mã khóa học là bắt buộc.',
            'course_id.integer' => 'Mã khóa học không hợp lệ.',
            'course_id.min' => 'Mã khóa học không hợp lệ.',
            'strict.boolean' => 'Tham số strict không hợp lệ.',
        ];
    }
}