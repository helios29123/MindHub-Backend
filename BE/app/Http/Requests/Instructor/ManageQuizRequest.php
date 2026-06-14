<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class ManageQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'course_id' => ['sometimes', 'integer', 'exists:courses,id'],
            'lesson_id' => ['sometimes', 'integer', 'exists:lessons,id'],
            'status' => ['sometimes', 'string', 'in:draft,published,hidden'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'per_page.integer' => 'Số dòng mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số dòng mỗi trang phải lớn hơn hoặc bằng 1.',
            'per_page.max' => 'Số dòng mỗi trang không được vượt quá 100.',
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.exists' => 'Khóa học không tồn tại.',
            'lesson_id.integer' => 'Bài học không hợp lệ.',
            'lesson_id.exists' => 'Bài học không tồn tại.',
            'status.in' => 'Trạng thái quiz không hợp lệ.',
        ];
    }

    public function validationData(): array
    {
        return $this->query();
    }
}