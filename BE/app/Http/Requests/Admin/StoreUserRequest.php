<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:admin,instructor,learner'],
            'status' => ['nullable', 'in:active,inactive,locked'],
            'locked_reason' => ['nullable', 'string'],
        ];
    }
}
