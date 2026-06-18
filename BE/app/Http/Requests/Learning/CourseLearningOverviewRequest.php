<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class CourseLearningOverviewRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $data = [
            'course_id' => $this->route('courseId'),
        ];

        foreach (['include_sections', 'include_next_lesson'] as $key) {
            if ($this->has($key)) {
                $booleanValue = filter_var(
                    $this->query($key),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );

                if ($booleanValue !== null) {
                    $data[$key] = $booleanValue;
                }
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
            'include_sections' => ['nullable', 'boolean'],
            'include_next_lesson' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Mã khóa học là bắt buộc.',
            'course_id.integer' => 'Mã khóa học không hợp lệ.',
            'course_id.min' => 'Mã khóa học không hợp lệ.',
            'include_sections.boolean' => 'Tham số include_sections không hợp lệ.',
            'include_next_lesson.boolean' => 'Tham số include_next_lesson không hợp lệ.',
        ];
    }
}