<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class CourseLearnerQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|in:active,completed,cancelled,expired',
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|in:id,full_name,email,status,last_accessed_at,completed_at,created_at',
            'sort_direction' => 'nullable|in:asc,desc',
        ];
    }
}
