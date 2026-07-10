<?php
namespace App\Http\Requests\Interaction;
use Illuminate\Foundation\Http\FormRequest;
final class InstructorQuestionLessonOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
    public function messages(): array
    {
        return [
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.exists' => 'Khóa học không tồn tại.',
            'search.max' => 'Từ khóa tìm kiếm không được vượt quá 255 ký tự.',
            'page.integer' => 'Trang không hợp lệ.',
            'page.min' => 'Trang phải lớn hơn 0.',
            'per_page.integer' => 'Số dòng mỗi trang không hợp lệ.',
            'per_page.min' => 'Số dòng mỗi trang phải lớn hơn 0.',
            'per_page.max' => 'Số dòng mỗi trang không được vượt quá 100.',
        ];
    }
}