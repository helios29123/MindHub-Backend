<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;

class LoginRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email hoặc Số điện thoại không được để trống.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.string' => 'Mật khẩu phải là chuỗi.',

            'device_name.string' => 'Tên thiết bị phải là chuỗi.',
            'device_name.max' => 'Tên thiết bị không được vượt quá 255 ký tự.',
        ];
    }
}
