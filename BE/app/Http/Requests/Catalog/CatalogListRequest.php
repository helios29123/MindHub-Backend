<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class CatalogListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'Tham số không hợp lệ.',
            'page.min' => 'Tham số không hợp lệ.',
            'per_page.integer' => 'Tham số không hợp lệ.',
            'per_page.min' => 'Tham số không hợp lệ.',
            'per_page.max' => 'Tham số không hợp lệ.',
        ];
    }
}
