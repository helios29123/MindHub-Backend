<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class AdminPayoutBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return ['period_month' => ['required', 'integer', 'min:1', 'max:12'], 'period_year' => ['required', 'integer', 'min:2020', 'max:2100'], 'note' => ['nullable', 'string', 'max:1000']];
    }
}
