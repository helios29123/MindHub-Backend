<?php

namespace App\Http\Requests\Marketing;

use App\Helpers\ApiResponse;
use App\Http\Requests\BaseApiRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BannerRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'image_url' => ['required', 'string', 'max:500'],
            'target_url' => ['nullable', 'string', 'max:500'],
            'position' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_at.after_or_equal' => 'Thời gian banner không hợp lệ.',
            'status.in' => 'Trạng thái banner không hợp lệ.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();
        $message = 'Dữ liệu không hợp lệ.';

        if ($errors->has('status')) {
            $message = 'Trạng thái banner không hợp lệ.';
        } elseif ($errors->has('end_at')) {
            $message = 'Thời gian banner không hợp lệ.';
        }

        throw new HttpResponseException(
            ApiResponse::error(
                $message,
                $errors->toArray(),
                422
            )
        );
    }
}
