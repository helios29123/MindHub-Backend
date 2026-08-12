<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at'),
            ],
            'password' => ['sometimes', 'string', 'min:8', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'role' => ['sometimes', 'in:admin,instructor,learner'],
            'status' => ['sometimes', 'in:active,inactive,locked'],
            'locked_reason' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
