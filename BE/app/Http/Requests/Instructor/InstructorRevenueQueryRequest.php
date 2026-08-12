<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class InstructorRevenueQueryRequest extends FormRequest
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
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'course_id' => ['sometimes', 'integer', 'exists:courses,id'],
            'month' => ['sometimes', 'integer', 'between:1,12'],
            'year' => ['sometimes', 'integer', 'between:2000,2100'],
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
            'date_from.date' => 'Ngày bắt đầu không hợp lệ.',
            'date_to.date' => 'Ngày kết thúc không hợp lệ.',
            'date_to.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.exists' => 'Khóa học không tồn tại.',
            'month.integer' => 'Tháng phải là số nguyên.',
            'month.between' => 'Tháng phải nằm trong khoảng 1 đến 12.',
            'year.integer' => 'Năm phải là số nguyên.',
            'year.between' => 'Năm không hợp lệ.',
        ];
    }

    public function validationData(): array
    {
        return $this->query();
    }
}