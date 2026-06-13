<?php

namespace App\Http\Requests\Learning;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LearningLogsRequest extends FormRequest
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
            'status' => ['nullable', 'string', 'in:not_started,in_progress,completed'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'Dữ liệu không hợp lệ.',
            'page.min' => 'Dữ liệu không hợp lệ.',
            'per_page.integer' => 'Dữ liệu không hợp lệ.',
            'per_page.min' => 'Dữ liệu không hợp lệ.',
            'per_page.max' => 'Dữ liệu không hợp lệ.',
            'status.in' => 'Dữ liệu không hợp lệ.',
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
