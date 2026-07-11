<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;

class RegisterInstructorRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Thông tin hồ sơ giảng viên
            'bio' => ['nullable', 'string', 'min:30'],
            'expertise' => ['nullable', 'string', 'max:1000'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'level' => ['nullable', 'string', 'max:50'],

            // Thông tin tài khoản nhận tiền
            'bank_provider' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',

            'bio.min' => 'Mô tả bản thân cần ít nhất 30 ký tự để admin có cơ sở duyệt giảng viên.',
            'experience_years.max' => 'Số năm kinh nghiệm không hợp lệ.',
        ];
    }
}
