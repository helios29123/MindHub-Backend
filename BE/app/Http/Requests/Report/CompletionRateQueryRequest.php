<?php
namespace App\Http\Requests\Report;
use Illuminate\Foundation\Http\FormRequest;
class CompletionRateQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'date_from' => ['nullable', 'date', 'before_or_equal:date_to'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'min:2000'],
        ];
    }
    public function messages(): array
    {
        return [
            'date_from.before_or_equal' => 'Khoảng thời gian lọc không hợp lệ.',
            'date_to.after_or_equal' => 'Khoảng thời gian lọc không hợp lệ.',
            'course_id.exists' => 'Khóa học không tồn tại.',
            'month.min' => 'Tháng không hợp lệ.',
            'month.max' => 'Tháng không hợp lệ.',
        ];
    }
}