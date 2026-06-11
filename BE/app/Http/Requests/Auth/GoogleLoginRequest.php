<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;

class GoogleLoginRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'google_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'google_token.required' => 'Google token không được để trống.',
            'google_token.string' => 'Google token phải là chuỗi.',
            'device_name.string' => 'Tên thiết bị phải là chuỗi.',
            'device_name.max' => 'Tên thiết bị không được vượt quá 255 ký tự.',
        ];
    }
}
