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
            'lesson_type' => ['sometimes', 'required', 'string', 'in:video,text,document'],
            'content' => ['nullable', 'required_if:lesson_type,text', 'string'],
            'video_url' => ['nullable', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if (empty($value)) return;
                if (str_starts_with($value, 'blob:')) {
                    $fail('Vui lòng tải lên video bài học hợp lệ.');
                    return;
                }
                if (preg_match('/^[a-zA-Z]:\\\\/', $value)) {
                    $fail('Vui lòng tải lên video bài học hợp lệ.');
                    return;
                }
                $isValidUrl = filter_var($value, FILTER_VALIDATE_URL) !== false;
                $isValidRelativePath = str_starts_with($value, '/') || str_starts_with($value, 'instructor/') || str_starts_with($value, 'videos/') || str_starts_with($value, 'storage/');
                if (!$isValidUrl && !$isValidRelativePath) {
                    $fail('Vui lòng tải lên video bài học hợp lệ.');
                }
            }],
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
            'video_url.url' => 'Vui lòng tải lên video bài học hợp lệ.',
        ];
    }
}
