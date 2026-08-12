<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectionRequest extends FormRequest
{
    private const ALLOWED_STATUSES = ["draft", "published", "hidden"];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "course_id" => [
                "required",
                "integer",
                Rule::exists("courses", "id")->whereNull("deleted_at"),
            ],
            "title" => ["required", "string", "max:255"],
            "description" => ["nullable", "string"],
            "sort_order" => ["nullable", "integer", "min:0"],
            "status" => ["nullable", Rule::in(self::ALLOWED_STATUSES)],
        ];
    }

    public function messages(): array
    {
        return [
            "course_id.required" => "Khóa học là bắt buộc.",
            "course_id.exists" => "Khóa học không tồn tại.",
            "title.required" => "Tên chương học là bắt buộc.",
            "title.max" => "Tên chương học không được vượt quá 255 ký tự.",
            "sort_order.integer" => "Thứ tự chương học phải là số nguyên.",
            "sort_order.min" => "Thứ tự chương học không được âm.",
            "status.in" => "Trạng thái chương học không hợp lệ.",
        ];
    }
}
