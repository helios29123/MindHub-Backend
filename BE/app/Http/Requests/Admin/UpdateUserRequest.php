<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^(0|\+84)(3|5|7|8|9)[0-9]{8}$/'],
            'role' => ['sometimes', 'in:admin,instructor,learner'],
            'status' => ['sometimes', 'in:active,inactive,locked'],
            'locked_reason' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng bởi một tài khoản khác.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'phone.regex' => 'Số điện thoại không đúng định dạng số di động Việt Nam (ví dụ: 0912345678).',
            'role.in' => 'Vai trò không hợp lệ (học viên, giảng viên hoặc quản trị viên).',
            'status.in' => 'Trạng thái tài khoản không hợp lệ.',
            'locked_reason.max' => 'Lý do khóa không được vượt quá 1000 ký tự.',
        ];
    }
}
