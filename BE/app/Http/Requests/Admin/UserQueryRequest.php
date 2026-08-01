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
            'email_verified' => ['nullable', 'in:verified,unverified'],
            'no_login' => ['nullable', 'in:true,false,1,0'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort_by' => ['nullable', 'in:id,full_name,email,role,status,created_at,last_login_at,newest,oldest,name_asc,name_desc,last_login'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
        ];
    }
}
