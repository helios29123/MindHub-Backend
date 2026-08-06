<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'instructor';
    }

    public function rules(): array
    {
        $courseId = $this->route('id');

        return [
            'id' => ['prohibited'],
            'instructor_id' => ['prohibited'],
            'status' => ['prohibited'],
            'is_featured' => ['prohibited'],
            'total_duration_seconds' => ['prohibited'],
            'published_at' => ['prohibited'],
            'admin_reject_reason' => ['prohibited'],
            'deleted_at' => ['prohibited'],

            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('courses', 'slug')->ignore($courseId)->whereNull('deleted_at'),
            ],
            'short_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'nullable', 'string'],
            'thumbnail_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'intro_video_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'original_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'has_discount' => ['sometimes', 'nullable', 'boolean'],
            'discount_percent' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:99', 'required_if:has_discount,true', 'required_if:has_discount,1'],
            'sale_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'level' => ['sometimes', Rule::in(['beginner', 'intermediate', 'advanced', 'all_levels'])],
            'language' => ['sometimes', 'nullable', 'string', 'max:20'],
            'requirements' => ['sometimes', 'nullable', 'string'],
            'outcomes' => ['sometimes', 'nullable', 'string'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('categories', 'id')->where('status', 'active')->whereNull('deleted_at'),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedFields = [
                'title',
                'slug',
                'short_description',
                'description',
                'thumbnail_url',
                'intro_video_url',
                'original_price',
                'price',
                'has_discount',
                'discount_percent',
                'sale_price',
                'level',
                'language',
                'requirements',
                'outcomes',
                'category_ids',
            ];

            $hasUpdateData = collect($allowedFields)->contains(
                fn (string $field): bool => $this->has($field)
            );

            if (!$hasUpdateData) {
                $validator->errors()->add('payload', 'Cần ít nhất một trường hợp lệ để cập nhật.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'instructor_id.prohibited' => 'Không được truyền instructor_id.',
            'status.prohibited' => 'Không được sửa status trực tiếp.',
            'is_featured.prohibited' => 'Không được tự set khóa học nổi bật.',
            'published_at.prohibited' => 'Không được tự set thời gian published.',
            'admin_reject_reason.prohibited' => 'Không được tự set lý do từ chối.',

            'slug.unique' => 'Slug khóa học đã tồn tại.',
            'slug.alpha_dash' => 'Slug chỉ được chứa chữ, số, dấu gạch ngang và gạch dưới.',
            'price.numeric' => 'Giá gốc khóa học phải là số.',
            'price.min' => 'Giá gốc khóa học không được âm.',
            'discount_percent.integer' => 'Phần trăm giảm giá phải là số nguyên.',
            'discount_percent.min' => 'Phần trăm giảm giá phải từ 1% đến 99%.',
            'discount_percent.max' => 'Phần trăm giảm giá phải từ 1% đến 99%.',
            'discount_percent.required_if' => 'Vui lòng nhập phần trăm giảm giá từ 1% đến 99%.',
            'sale_price.numeric' => 'Giá khuyến mãi phải là số.',
            'sale_price.min' => 'Giá khuyến mãi không được âm.',
            'level.in' => 'Cấp độ khóa học không hợp lệ.',
            'category_ids.*.integer' => 'Mã danh mục khóa học phải là số nguyên.',
            'category_ids.*.exists' => 'Danh mục không tồn tại hoặc đã bị tắt.',
        ];
    }
}