<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
final class LearnerQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'status' => ['nullable', Rule::in(['all', 'active', 'completed'])],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in([
                'newest',
                'oldest',
                'progress_asc',
                'progress_desc',
                'last_accessed_desc',
                'last_accessed_asc',
            ])],
            'enrolled_from' => ['nullable', 'date'],
            'enrolled_to' => ['nullable', 'date', 'after_or_equal:enrolled_from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
    public function messages(): array
    {
        return [
            'status.in' => 'Trạng thái học viên không hợp lệ.',
            'sort.in' => 'Kiểu sắp xếp không hợp lệ.',
            'per_page.max' => 'Số dòng mỗi trang không được vượt quá 50.',
            'enrolled_to.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
        ];
    }
}