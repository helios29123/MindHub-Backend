<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
final class ManageLessonsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'course_section_id' => ['nullable', 'integer', 'exists:course_sections,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'lesson_type' => ['nullable', 'string', 'in:video,text'],
            'status' => ['nullable', 'string', 'in:draft,published,hidden'],
            'sort_by' => ['nullable', 'string', 'in:sort_order,created_at,title,status'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
    public function messages(): array
    {
        return [
            '*.exists' => 'Không tìm thấy dữ liệu.',
            '*.in' => 'Tham số không hợp lệ.',
            '*.integer' => 'Tham số không hợp lệ.',
            '*.min' => 'Tham số không hợp lệ.',
            '*.max' => 'Tham số không hợp lệ.',
        ];
    }
}