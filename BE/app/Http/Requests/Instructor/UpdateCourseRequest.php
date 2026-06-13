<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCourseRequest extends FormRequest
{
    private const ALLOWED_STATUSES = ["draft", "pending_review", "hidden"];

    private const ALLOWED_LEVELS = [
        "beginner",
        "intermediate",
        "advanced",
        "all_levels",
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->route("id");

        return [
            "title" => ["sometimes", "string", "max:255"],

            "slug" => [
                "sometimes",
                "string",
                "max:255",
                Rule::unique("courses", "slug")
                    ->ignore($courseId)
                    ->whereNull("deleted_at"),
            ],

            "short_description" => ["sometimes", "nullable", "string"],
            "description" => ["sometimes", "nullable", "string"],

            "thumbnail_url" => ["sometimes", "nullable", "url", "max:2048"],
            "intro_video_url" => ["sometimes", "nullable", "url", "max:2048"],

            "price" => ["sometimes", "numeric", "min:0"],
            "sale_price" => ["sometimes", "nullable", "numeric", "min:0"],

            "level" => ["sometimes", Rule::in(self::ALLOWED_LEVELS)],

            "language" => ["sometimes", "nullable", "string", "max:20"],

            "requirements" => ["sometimes", "nullable"],
            "outcomes" => ["sometimes", "nullable"],

            "status" => ["sometimes", Rule::in(self::ALLOWED_STATUSES)],

            "category_ids" => ["sometimes", "array"],
            "category_ids.*" => [
                "integer",
                "distinct",
                Rule::exists("categories", "id")
                    ->where("status", "active")
                    ->whereNull("deleted_at"),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedFields = [
                "title",
                "slug",
                "short_description",
                "description",
                "thumbnail_url",
                "intro_video_url",
                "price",
                "sale_price",
                "level",
                "language",
                "requirements",
                "outcomes",
                "status",
                "category_ids",
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
            "slug.unique" => "Slug khóa học đã tồn tại.",
            "thumbnail_url.url" => "URL thumbnail không hợp lệ.",
            "intro_video_url.url" => "URL video giới thiệu không hợp lệ.",
            "price.numeric" => "Giá khóa học phải là số.",
            "price.min" => "Giá khóa học không được âm.",
            "sale_price.numeric" => "Giá khuyến mãi phải là số.",
            "sale_price.min" => "Giá khuyến mãi không được âm.",
            "level.in" => "Cấp độ khóa học không hợp lệ.",
            "status.in" => "Trạng thái khóa học không hợp lệ.",
            "category_ids.array" => "Danh mục khóa học không hợp lệ.",
            "category_ids.*.exists" =>
                "Danh mục không hợp lệ hoặc đang bị vô hiệu hóa.",
            "category_ids.*.distinct" => "Danh mục không được trùng lặp.",
        ];
    }
}
