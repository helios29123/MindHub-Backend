<?php
namespace App\Http\Requests\Interaction;
use Illuminate\Foundation\Http\FormRequest;
final class InstructorQuestionSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.exists' => 'Khóa học không tồn tại.',
            'lesson_id.integer' => 'Bài học không hợp lệ.',
            'lesson_id.exists' => 'Bài học không tồn tại.',
        ];
    }
}