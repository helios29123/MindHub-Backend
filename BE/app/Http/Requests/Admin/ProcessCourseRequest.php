<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class ProcessCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return ['reason' => ['nullable', 'string', 'max:1000'], 'course_ids' => ['nullable', 'array'], 'course_ids.*' => ['integer', 'exists:courses,id']];
    }
}
