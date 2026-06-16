<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'in:admin,instructor,learner'],
            'status' => ['nullable', 'in:active,inactive,locked'],
            'sort_by' => ['nullable', 'in:id,full_name,email,role,status,created_at,last_login_at'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
        ];
    }
}
