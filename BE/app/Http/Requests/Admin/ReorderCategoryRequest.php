<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReorderCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
            ],
            'items.*.parent_id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
            ],
            'items.*.sort_order' => ['required', 'integer', 'min:1'],
        ];
    }
}