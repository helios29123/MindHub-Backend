<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'instructor_id' => ['prohibited'],
            'status' => ['prohibited'],
            'is_featured' => ['prohibited'],
            'total_duration_seconds' => ['prohibited'],
            'published_at' => ['prohibited'],
            'admin_reject_reason' => ['prohibited'],
            'deleted_at' => ['prohibited'],

            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('courses', 'slug')->whereNull('deleted_at'),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'thumbnail_url' => ['nullable', 'string', 'max:500'],
            'intro_video_url' => ['nullable', 'string', 'max:500'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'has_discount' => ['nullable', 'boolean'],
            'discount_percent' => ['nullable', 'integer', 'min:1', 'max:99', 'required_if:has_discount,true', 'required_if:has_discount,1'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced', 'all_levels'])],
            'language' => ['nullable', 'string', 'max:20'],
            'requirements' => ['nullable', 'string'],
            'outcomes' => ['nullable', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('categories', 'id')->where('status', 'active')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'instructor_id.prohibited' => 'Không được truyền instructor_id.',
            'status.prohibited' => 'Không được tự set trạng thái khóa học.',
            'is_featured.prohibited' => 'Không được tự set khóa học nổi bật.',
            'published_at.prohibited' => 'Không được tự set thời gian published.',
            'admin_reject_reason.prohibited' => 'Không được tự set lý do từ chối.',

            'title.required' => 'Tên khóa học là bắt buộc.',
            'title.max' => 'Tên khóa học không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug khóa học đã tồn tại.',
            'slug.alpha_dash' => 'Slug chỉ được chứa chữ, số, dấu gạch ngang và gạch dưới.',
            'price.required' => 'Giá gốc khóa học là bắt buộc.',
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