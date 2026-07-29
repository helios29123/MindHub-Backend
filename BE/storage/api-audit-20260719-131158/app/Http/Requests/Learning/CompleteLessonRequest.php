<?php

namespace App\Http\Requests\Learning;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CompleteLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'completed' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'completed.required' => 'Dữ liệu không hợp lệ.',
            'completed.boolean' => 'Dữ liệu không hợp lệ.',
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
