<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class RevenueReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'course_id' => 'nullable|integer|exists:courses,id,deleted_at,NULL',
            'instructor_id' => 'nullable|integer|exists:users,id,deleted_at,NULL',
            'group_by' => 'nullable|in:day,month',
            'sort_by' => 'nullable|in:date,gross_amount,instructor_amount,platform_fee_amount,order_count',
            'sort_direction' => 'nullable|in:asc,desc',
        ];
    }
}
