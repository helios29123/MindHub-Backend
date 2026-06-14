<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAdminCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'status' => ['sometimes', 'in:draft,pending_review,approved,rejected,published,hidden'],
            'is_featured' => ['sometimes', 'boolean'],
            'admin_reject_reason' => ['sometimes', 'nullable', 'string'],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:courses,slug,' . $id . ',id,deleted_at,NULL'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'thumbnail_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'intro_video_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'level' => ['sometimes', 'in:beginner,intermediate,advanced,all_levels'],
            'language' => ['sometimes', 'nullable', 'string', 'max:20'],
            'requirements' => ['sometimes', 'nullable'],
            'outcomes' => ['sometimes', 'nullable'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedFields = [
                'status',
                'is_featured',
                'admin_reject_reason',
                'title',
                'slug',
                'short_description',
                'description',
                'thumbnail_url',
                'intro_video_url',
                'price',
                'sale_price',
                'level',
                'language',
                'requirements',
                'outcomes'
            ];

            $hasUpdateData = collect($allowedFields)->contains(
                fn(string $field): bool => $this->has($field),
            );

            if (!$hasUpdateData) {
                $validator->errors()->add('payload', 'Cần ít nhất một trường hợp lệ để cập nhật.');
            }
        });
    }
}
