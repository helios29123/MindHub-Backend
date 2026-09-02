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
        $minPrice = (int) config('course.min_price', 50000);

        return [
            'id' => ['prohibited'],
            'instructor_id' => ['prohibited'],
            'status' => ['prohibited'],
            'is_featured' => ['prohibited'],
            'total_duration_seconds' => ['prohibited'],
            'published_at' => ['prohibited'],
            'admin_reject_reason' => ['prohibited'],

            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('courses', 'slug'),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'thumbnail_url' => ['nullable', 'string', 'max:500'],
            'intro_video_url' => ['nullable', 'string', 'max:500'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:' . $minPrice],
            'has_discount' => ['nullable', 'boolean'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'course_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced', 'all_levels'])],
            'language' => ['nullable', 'string', 'max:20'],
            'requirements' => ['nullable'],
            'outcomes' => ['nullable'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('categories', 'id')->where('status', 'active'),
            ],
        ];
    }

    public function messages(): array
    {
        $minPriceFormatted = number_format((int) config('course.min_price', 50000), 0, ',', '.') . 'đ';

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
            'price.min' => "Giá bán của khóa học tối thiểu là {$minPriceFormatted}.",
            'sale_price.numeric' => 'Giá khuyến mãi phải là số.',
            'sale_price.min' => 'Giá khuyến mãi không được âm.',
            'level.in' => 'Cấp độ khóa học không hợp lệ.',
            'category_ids.*.integer' => 'Mã danh mục khóa học phải là số nguyên.',
            'category_ids.*.exists' => 'Danh mục không tồn tại hoặc đã bị tắt.',
        ];
    }
}