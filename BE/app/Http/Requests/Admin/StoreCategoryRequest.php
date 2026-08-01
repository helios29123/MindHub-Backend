<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug'),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
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