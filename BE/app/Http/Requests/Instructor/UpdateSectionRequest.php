<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSectionRequest extends FormRequest
{
    private const ALLOWED_STATUSES = ["draft", "published", "hidden"];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "title" => ["sometimes", "string", "max:255"],
            "description" => ["sometimes", "nullable", "string"],
            "sort_order" => ["sometimes", "integer", "min:0"],
            "status" => ["sometimes", Rule::in(self::ALLOWED_STATUSES)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedFields = ["title", "description", "sort_order", "status"];

            $hasUpdateData = collect($allowedFields)->contains(
                fn(string $field): bool => $this->has($field),
            );

            if (!$hasUpdateData) {
                $validator
                    ->errors()
                    ->add(
                        "payload",
                        "Cần ít nhất một trường hợp lệ để cập nhật.",
                    );
            }
        });
    }

    public function messages(): array
    {
        return [
            "title.max" => "Tên chương học không được vượt quá 255 ký tự.",
            "sort_order.integer" => "Thứ tự chương học phải là số nguyên.",
            "sort_order.min" => "Thứ tự chương học không được âm.",
            "status.in" => "Trạng thái chương học không hợp lệ.",
        ];
    }
}
