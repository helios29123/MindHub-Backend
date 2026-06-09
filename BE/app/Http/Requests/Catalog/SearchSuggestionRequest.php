<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class SearchSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            '*.integer' => 'Tham số không hợp lệ.',
            '*.min' => 'Tham số không hợp lệ.',
            '*.max' => 'Tham số không hợp lệ.',
            '*.string' => 'Tham số không hợp lệ.',
        ];
    }
}
