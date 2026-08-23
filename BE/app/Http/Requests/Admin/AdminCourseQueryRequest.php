<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminCourseQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,pending_review,approved,rejected,published,hidden'],
            'instructor_id' => ['nullable', 'integer', 'exists:users,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'level' => ['nullable', 'in:beginner,intermediate,advanced,all_levels'],
            'sort_by' => ['nullable', 'in:id,title,slug,status,price,sale_price,created_at,updated_at,published_at,gross_revenue,enrollment_count,average_rating'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
        ];
    }
}
