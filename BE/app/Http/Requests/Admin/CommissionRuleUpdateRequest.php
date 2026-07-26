<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class CommissionRuleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return ['instructor_rate' => ['required', 'numeric', 'min:0', 'max:100'], 'platform_rate' => ['required', 'numeric', 'min:0', 'max:100'], 'description' => ['nullable', 'string', 'max:1000'], 'is_active' => ['nullable', 'boolean']];
    }
}
