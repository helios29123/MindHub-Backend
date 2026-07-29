<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionQueryRequest extends FormRequest
{
    private const ALLOWED_STATUSES = ["draft", "published", "hidden"];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "page" => ["nullable", "integer", "min:1"],
            "per_page" => ["nullable", "integer", "min:1", "max:100"],
            "course_id" => [
                "nullable",
                "integer",
                Rule::exists("courses", "id")->whereNull("deleted_at"),
            ],
            "search" => ["nullable", "string", "max:255"],
            "status" => ["nullable", Rule::in(self::ALLOWED_STATUSES)],
            "sort_by" => [
                "nullable",
                Rule::in([
                    "id",
                    "title",
                    "status",
                    "sort_order",
                    "created_at",
                    "updated_at",
                ]),
            ],
            "sort_direction" => ["nullable", Rule::in(["asc", "desc"])],
        ];
    }

    public function messages(): array
    {
        return [
            "course_id.exists" => "Khóa học không tồn tại.",
            "status.in" => "Trạng thái chương học không hợp lệ.",
            "sort_by.in" => "Trường sắp xếp không hợp lệ.",
            "sort_direction.in" => "Chiều sắp xếp không hợp lệ.",
        ];
    }
}
