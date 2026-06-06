<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ tên không được để trống.',
            'full_name.string' => 'Họ tên phải là chuỗi.',
            'full_name.max' => 'Họ tên không được vượt quá 255 ký tự.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'phone.string' => 'Số điện thoại phải là chuỗi.',
            'phone.max' => 'Số điện thoại không được vượt quá 30 ký tự.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ];
    }
}
