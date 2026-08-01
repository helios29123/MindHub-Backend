<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = (int) $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'sort_order' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedFields = ['name', 'slug', 'parent_id', 'description', 'sort_order', 'status'];

            if (!collect($allowedFields)->contains(fn (string $field): bool => $this->has($field))) {
                $validator->errors()->add('payload', 'Cần ít nhất một trường hợp lệ để cập nhật.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang.',
            'slug.unique' => 'Slug danh mục đã tồn tại, kể cả trong danh mục đã xóa.',
            'parent_id.exists' => 'Danh mục cha không tồn tại hoặc đã bị xóa.',
        ];
    }
}