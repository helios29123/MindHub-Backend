<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateInstructorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "bio" => ["sometimes", "nullable", "string"],
            "expertise" => ["sometimes", "nullable", "string"],
            "experience_years" => ["sometimes", "nullable", "integer", "min:0", "max:80"],
            "level" => ["sometimes", "nullable", "string", "max:50"],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedFields = [
                "bio",
                "expertise",
                "experience_years",
                "level",
            ];

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
            "experience_years.integer" => "Số năm kinh nghiệm phải là số nguyên.",
            "experience_years.min" => "Số năm kinh nghiệm không được âm.",
            "experience_years.max" => "Số năm kinh nghiệm không được vượt quá 80.",
            "level.max" => "Cấp độ không được vượt quá 50 ký tự.",
        ];
    }
}
