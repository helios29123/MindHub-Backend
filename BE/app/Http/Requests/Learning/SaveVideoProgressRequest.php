<?php

namespace App\Http\Requests\Learning;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SaveVideoProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_second' => ['required', 'integer', 'min:0'],
            'duration_second' => ['nullable', 'integer', 'min:1'],
            'is_completed' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_second.required' => 'Dữ liệu không hợp lệ.',
            'current_second.integer' => 'Dữ liệu không hợp lệ.',
            'current_second.min' => 'Dữ liệu không hợp lệ.',
            'duration_second.integer' => 'Dữ liệu không hợp lệ.',
            'duration_second.min' => 'Dữ liệu không hợp lệ.',
            'is_completed.boolean' => 'Dữ liệu không hợp lệ.',
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
