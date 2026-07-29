<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
final class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'course_id' => ['sometimes', 'required', 'integer', 'exists:courses,id'],
            'course_section_id' => ['sometimes', 'required', 'integer', 'exists:course_sections,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'lesson_type' => ['sometimes', 'required', 'string', 'in:video,text'],
            'content' => ['nullable', 'required_if:lesson_type,text', 'string'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'video_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:draft,published,hidden'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
    public function messages(): array
    {
        return [
            '*.exists' => 'Không tìm thấy dữ liệu.',
            '*.in' => 'Tham số không hợp lệ.',
            'title.required' => 'Vui lòng nhập tiêu đề bài học.',
            'content.required_if' => 'Bài học dạng text bắt buộc phải có nội dung.',
            'video_url.url' => 'Đường dẫn video không hợp lệ.',
        ];
    }
}
