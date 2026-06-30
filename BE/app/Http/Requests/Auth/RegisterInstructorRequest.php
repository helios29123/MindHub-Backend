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
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Thông tin hồ sơ giảng viên
            'bio' => ['required', 'string', 'min:30'],
            'expertise' => ['required', 'string', 'max:1000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'level' => ['required', 'string', 'max:50'],

            // Thông tin tài khoản nhận tiền
            'bank_provider' => ['required', 'string', 'max:50'],
            'bank_account_number' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',

            'bio.required' => 'Mô tả bản thân không được để trống.',
            'bio.min' => 'Mô tả bản thân cần ít nhất 30 ký tự để admin có cơ sở duyệt giảng viên.',
            'expertise.required' => 'Chuyên môn giảng dạy không được để trống.',
            'experience_years.required' => 'Số năm kinh nghiệm không được để trống.',
            'experience_years.max' => 'Số năm kinh nghiệm không hợp lệ.',
            'level.required' => 'Cấp độ giảng viên không được để trống.',

            'bank_provider.required' => 'Tên ngân hàng/nhà cung cấp thanh toán không được để trống.',
            'bank_account_number.required' => 'Số tài khoản ngân hàng không được để trống.',
            'bank_account_name.required' => 'Tên chủ tài khoản ngân hàng không được để trống.',
        ];
    }
}
