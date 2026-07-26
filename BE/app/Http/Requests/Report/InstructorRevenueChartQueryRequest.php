<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class InstructorRevenueChartQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'preset' => ['nullable', 'in:day,week,month,year,custom'],
            'period' => ['nullable', 'in:day,week,month,year,custom'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'group_by' => ['nullable', 'in:day,month'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ];
    }
}