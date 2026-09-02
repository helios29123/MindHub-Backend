<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;

class RegisterLearnerRequest extends BaseApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => !empty($this->email) ? trim($this->email) : null,
            'phone' => !empty($this->phone) ? trim($this->phone) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required_without:phone', 'nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required_without:email', 'nullable', 'string', 'regex:/^(0|\+84)[1-9][0-9]{8}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ và tên không được để trống.',
            'full_name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required_without' => 'Vui lòng cung cấp Email hoặc Số điện thoại để đăng ký.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng. Vui lòng đăng nhập hoặc dùng email khác.',
            'phone.required_without' => 'Vui lòng cung cấp Email hoặc Số điện thoại để đăng ký.',
            'phone.regex' => 'Số điện thoại không đúng định dạng (VD: 0987654321).',
            'phone.unique' => 'Số điện thoại này đã được sử dụng bởi tài khoản khác.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp.',
        ];
    }
}
