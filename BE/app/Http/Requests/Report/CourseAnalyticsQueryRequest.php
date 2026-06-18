<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class CourseAnalyticsQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fromDate = $this->input('from_date');
            $toDate = $this->input('to_date');

            if ($fromDate && $toDate) {
                $from = \Carbon\Carbon::parse($fromDate);
                $to = \Carbon\Carbon::parse($toDate);
                
                if ($from->diffInDays($to) > 366) {
                    $validator->errors()->add('to_date', 'Khoảng thời gian không được vượt quá 366 ngày.');
                }
            }
        });
    }
}
