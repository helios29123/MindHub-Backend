<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class AdminRevenueQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:255'], 'instructor_id' => ['nullable', 'integer'], 'course_id' => ['nullable', 'integer'], 'status' => ['nullable', 'string'], 'sale_channel' => ['nullable', 'string'], 'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'], 'page' => ['nullable', 'integer', 'min:1'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']];
    }
}
