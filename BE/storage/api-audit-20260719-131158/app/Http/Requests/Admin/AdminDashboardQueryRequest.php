<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class AdminDashboardQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return ['date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'], 'period' => ['nullable', 'string', 'in:today,week,month,year,custom']];
    }
}
