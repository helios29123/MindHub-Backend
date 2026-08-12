<?php
namespace App\Http\Requests\Marketing;
use Illuminate\Foundation\Http\FormRequest;
final class InstructorCouponSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.exists' => 'Khóa học không tồn tại.',
        ];
    }
}