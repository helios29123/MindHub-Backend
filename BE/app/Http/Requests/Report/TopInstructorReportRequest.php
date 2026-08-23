<?php

namespace App\Http\Requests\Report;

use App\Http\Requests\BaseApiRequest;

class TopInstructorReportRequest extends BaseApiRequest
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
            'course_id' => 'nullable|integer|exists:courses,id',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'sort_by' => 'nullable|in:total_courses,total_sold,total_revenue,total_completed,completion_rate,last_activity_at',
            'sort_direction' => 'nullable|in:asc,desc',
            'timeframe' => 'nullable|string',
        ];
    }
}
