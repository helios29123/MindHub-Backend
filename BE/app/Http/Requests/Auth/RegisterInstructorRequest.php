<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;

class RegisterInstructorRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $inputEmail = strtolower(trim((string) $this->input('email')));
        $existingUser = !empty($inputEmail) ? \App\Models\User::where('email', $inputEmail)->first() : null;

        $isRejectedResubmit = false;
        if ($existingUser && $existingUser->role === \App\Models\User::ROLE_INSTRUCTOR) {
            $latestPayout = \App\Models\PayoutAccount::where('user_id', $existingUser->id)->orderByDesc('id')->first();
            if ($latestPayout?->status === 'disabled') {
                $isRejectedResubmit = true;
            }
        }

        $emailRules = ['required', 'string', 'email', 'max:255'];
        $phoneRules = ['required', 'string', 'regex:/^(0|\+84)[1-9][0-9]{8}$/'];

        if ($isRejectedResubmit && $existingUser) {
            $emailRules[] = \Illuminate\Validation\Rule::unique('users', 'email')->ignore($existingUser->id);
            $phoneRules[] = \Illuminate\Validation\Rule::unique('users', 'phone')->ignore($existingUser->id);
        } else {
            $emailRules[] = 'unique:users,email';
            $phoneRules[] = 'unique:users,phone';
        }

        return [
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => $emailRules,
            'phone' => $phoneRules,
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Thông tin hồ sơ giảng viên
            'bio' => ['nullable', 'string', 'min:30', 'max:5000'],
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
            'full_name.required' => 'Họ và tên không được để trống.',
            'full_name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required' => 'Địa chỉ email không được để trống.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng. Vui lòng đăng nhập hoặc dùng email khác.',
            'phone.required' => 'Số điện thoại xác thực liên hệ là bắt buộc đối với Giảng viên.',
            'phone.regex' => 'Số điện thoại không đúng định dạng (VD: 0987654321).',
            'phone.unique' => 'Số điện thoại này đã được sử dụng bởi tài khoản khác.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp.',

            'bio.min' => 'Mô tả bản thân cần ít nhất 30 ký tự để Admin có cơ sở duyệt giảng viên.',
            'bio.max' => 'Mô tả bản thân không được vượt quá 5000 ký tự.',
            'experience_years.integer' => 'Số năm kinh nghiệm phải là số nguyên.',
            'experience_years.min' => 'Số năm kinh nghiệm không thể âm.',
            'experience_years.max' => 'Số năm kinh nghiệm tối đa là 80 năm.',
        ];
    }
}
