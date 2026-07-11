<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class InstructorAccountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'user_id' => ['prohibited'],
            'instructor_id' => ['prohibited'],
            'email' => ['prohibited'],
            'phone' => ['prohibited'],
            'role' => ['prohibited'],
            'status' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'last_login_at' => ['prohibited'],
            'locked' => ['prohibited'],
            'locked_reason' => ['prohibited'],
            'password_hash' => ['prohibited'],
            'password_reset' => ['prohibited'],
            'oauth_account_login' => ['prohibited'],
            'deleted_at' => ['prohibited'],

            'full_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (trim((string) $this->input('full_name')) === '') {
                $validator->errors()->add(
                    'full_name',
                    'Họ tên không được để trống.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ tên là bắt buộc.',
            'full_name.string' => 'Họ tên phải là chuỗi.',
            'full_name.max' => 'Họ tên không được vượt quá 255 ký tự.',

            'user_id.prohibited' => 'Không được truyền user_id.',
            'instructor_id.prohibited' => 'Không được truyền instructor_id.',
            'email.prohibited' => 'Không được cập nhật email trực tiếp từ API này.',
            'phone.prohibited' => 'Cập nhật số điện thoại cần cơ chế xác thực OTP và chưa triển khai trong GD1.',
            'role.prohibited' => 'Không được cập nhật vai trò.',
            'status.prohibited' => 'Không được cập nhật trạng thái tài khoản.',
            'email_verified_at.prohibited' => 'Không được cập nhật trạng thái xác thực email.',
            'last_login_at.prohibited' => 'Không được cập nhật thời gian đăng nhập gần nhất.',
            'locked.prohibited' => 'Không được cập nhật trạng thái khóa.',
            'locked_reason.prohibited' => 'Không được cập nhật lý do khóa.',
            'password_hash.prohibited' => 'Không được cập nhật mật khẩu từ API này.',
            'password_reset.prohibited' => 'Không được cập nhật dữ liệu đặt lại mật khẩu.',
            'oauth_account_login.prohibited' => 'Không được cập nhật tài khoản OAuth.',
            'deleted_at.prohibited' => 'Không được cập nhật deleted_at.',
        ];
    }
}