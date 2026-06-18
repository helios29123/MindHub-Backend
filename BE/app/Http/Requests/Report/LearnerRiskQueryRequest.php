<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class LearnerRiskQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'risk_level' => ['nullable', 'string', 'in:low,medium,high'],
            'inactive_days' => ['nullable', 'integer', 'min:3', 'max:90'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
