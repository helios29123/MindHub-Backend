<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class LearningDashboardRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('include_alerts')) {
            $booleanValue = filter_var(
                $this->query('include_alerts'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($booleanValue !== null) {
                $this->merge([
                    'include_alerts' => $booleanValue,
                ]);
            }
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limit_recent' => ['nullable', 'integer', 'min:1', 'max:20'],
            'include_alerts' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'limit_recent.integer' => 'Số lượng học gần đây không hợp lệ.',
            'limit_recent.min' => 'Số lượng học gần đây tối thiểu là 1.',
            'limit_recent.max' => 'Số lượng học gần đây tối đa là 20.',
            'include_alerts.boolean' => 'Tham số include_alerts không hợp lệ.',
        ];
    }
}