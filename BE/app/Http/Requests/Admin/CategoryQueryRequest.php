<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class CategoryQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,all'],
            'type' => ['nullable', 'in:root,child'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'empty' => ['nullable', 'in:true,false,1,0'],
            'sort_by' => [
                'nullable',
                'in:newest,oldest,name_asc,name_desc,sort_order_asc,sort_order_desc,courses_desc,id,name,slug,status,sort_order,created_at,updated_at',
            ],
            'sort_direction' => ['nullable', 'in:asc,desc'],
        ];
    }
}