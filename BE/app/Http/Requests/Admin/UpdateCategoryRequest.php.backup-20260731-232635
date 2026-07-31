<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:categories,slug,' . $id . ',id,deleted_at,NULL'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id,deleted_at,NULL'],
            'description' => ['sometimes', 'nullable', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedFields = [
                'name',
                'slug',
                'parent_id',
                'description',
                'sort_order',
                'status',
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
