<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
final class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'course_section_id' => ['required', 'integer', 'exists:course_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'lesson_type' => ['required', 'string', 'in:video,text'],
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
            'course_id.required' => 'Vui lòng chọn khóa học.',
            'course_id.exists' => 'Không tìm thấy dữ liệu.',
            'course_section_id.required' => 'Vui lòng chọn chương/phần học.',
            'course_section_id.exists' => 'Không tìm thấy dữ liệu.',
            'title.required' => 'Vui lòng nhập tiêu đề bài học.',
            'lesson_type.required' => 'Vui lòng chọn loại bài học.',
            'lesson_type.in' => 'Tham số không hợp lệ.',
            'content.required_if' => 'Bài học dạng text bắt buộc phải có nội dung.',
            'video_url.url' => 'Đường dẫn video không hợp lệ.',
            'status.in' => 'Tham số không hợp lệ.',
        ];
    }
}
