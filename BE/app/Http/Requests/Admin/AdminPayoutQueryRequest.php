<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class AdminPayoutQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', 'string'], 'period_month' => ['nullable', 'integer', 'min:1', 'max:12'], 'period_year' => ['nullable', 'integer', 'min:2020', 'max:2100'], 'page' => ['nullable', 'integer', 'min:1'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']];
    }
}
