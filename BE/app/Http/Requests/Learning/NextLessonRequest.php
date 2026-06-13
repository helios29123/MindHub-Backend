<?php

namespace App\Http\Requests\Learning;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class NextLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'Dữ liệu không hợp lệ.',
            'id.integer' => 'Dữ liệu không hợp lệ.',
            'id.min' => 'Dữ liệu không hợp lệ.',
            'page.integer' => 'Dữ liệu không hợp lệ.',
            'page.min' => 'Dữ liệu không hợp lệ.',
            'per_page.integer' => 'Dữ liệu không hợp lệ.',
            'per_page.min' => 'Dữ liệu không hợp lệ.',
            'per_page.max' => 'Dữ liệu không hợp lệ.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Dữ liệu không hợp lệ.',
                $validator->errors()->toArray(),
                422
            )
        );
    }
}
