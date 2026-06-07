<?php

namespace App\Http\Requests\User;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class MeProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->query->count() > 0) {
                $validator->errors()->add('query', 'Tham số không hợp lệ.');
            }

            if ($this->request->count() > 0) {
                $validator->errors()->add('body', 'API này không nhận body.');
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                message: 'Tham số không hợp lệ.',
                errors: $validator->errors()->toArray(),
                status: 422
            )
        );
    }
}