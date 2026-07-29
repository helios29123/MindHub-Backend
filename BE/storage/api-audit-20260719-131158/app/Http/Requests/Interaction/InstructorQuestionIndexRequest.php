<?php
namespace App\Http\Requests\Interaction;
use Illuminate\Foundation\Http\FormRequest;
final class InstructorQuestionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:all,unanswered,answered'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:newest,oldest'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
    public function messages(): array
    {
        return [
            'status.in' => 'Trạng thái câu hỏi không hợp lệ.',
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.exists' => 'Khóa học không tồn tại.',
            'lesson_id.integer' => 'Bài học không hợp lệ.',
            'lesson_id.exists' => 'Bài học không tồn tại.',
            'search.max' => 'Từ khóa tìm kiếm không được vượt quá 255 ký tự.',
            'sort.in' => 'Kiểu sắp xếp không hợp lệ.',
            'page.integer' => 'Trang không hợp lệ.',
            'page.min' => 'Trang phải lớn hơn 0.',
            'per_page.integer' => 'Số dòng mỗi trang không hợp lệ.',
            'per_page.min' => 'Số dòng mỗi trang phải lớn hơn 0.',
            'per_page.max' => 'Số dòng mỗi trang không được vượt quá 50.',
        ];
    }
}