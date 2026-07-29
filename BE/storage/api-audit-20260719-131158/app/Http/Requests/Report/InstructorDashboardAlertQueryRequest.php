<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class InstructorDashboardAlertQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}