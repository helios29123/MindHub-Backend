<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class InstructorCourseIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:draft,pending_review,approved,rejected,published,hidden'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:newest,oldest,title_asc,title_desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}