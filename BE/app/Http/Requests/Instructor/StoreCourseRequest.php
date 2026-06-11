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
            'title' => ['required', 'string', 'max:255'],

            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('courses', 'slug')->whereNull('deleted_at'),
            ],

            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],

            'thumbnail_url' => ['nullable', 'url', 'max:500'],
            'intro_video_url' => ['nullable', 'url', 'max:500'],

            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],

            'level' => [
                'nullable',
                Rule::in([
                    'beginner',
                    'intermediate',
                    'advanced',
                    'all_levels',
                ]),
            ],

            'language' => ['nullable', 'string', 'max:50'],

            'requirements' => ['nullable', 'string'],
            'outcomes' => ['nullable', 'string'],

            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')
                    ->where('status', 'active')
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tên khóa học là bắt buộc.',
            'title.max' => 'Tên khóa học không được vượt quá 255 ký tự.',

            'slug.required' => 'Slug khóa học là bắt buộc.',
            'slug.unique' => 'Slug khóa học đã tồn tại.',
            'slug.alpha_dash' => 'Slug chỉ được chứa chữ, số, dấu gạch ngang và gạch dưới.',

            'price.required' => 'Giá khóa học là bắt buộc.',
            'price.numeric' => 'Giá khóa học phải là số.',
            'price.min' => 'Giá khóa học không được nhỏ hơn 0.',

            'sale_price.numeric' => 'Giá khuyến mãi phải là số.',
            'sale_price.min' => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'sale_price.lte' => 'Giá khuyến mãi không được lớn hơn giá gốc.',

            'level.in' => 'Cấp độ khóa học không hợp lệ.',

            'thumbnail_url.url' => 'Đường dẫn ảnh đại diện không hợp lệ.',
            'intro_video_url.url' => 'Đường dẫn video giới thiệu không hợp lệ.',

            'category_ids.array' => 'Danh mục khóa học phải là một danh sách.',
            'category_ids.*.exists' => 'Danh mục khóa học không hợp lệ hoặc đang bị ẩn.',
        ];
    }
}