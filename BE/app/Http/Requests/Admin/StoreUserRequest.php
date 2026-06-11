<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "full_name" => ["required", "string", "max:255"],

            "email" => [
                "required",
                "email",
                "max:255",
                Rule::unique("users", "email")->whereNull("deleted_at"),
            ],

            "password" => ["required", "string", "min:8", "max:255"],

            "phone" => ["nullable", "string", "max:20"],

            "role" => [
                "required",
                Rule::in([
                    User::ROLE_ADMIN,
                    User::ROLE_INSTRUCTOR,
                    User::ROLE_LEARNER,
                ]),
            ],

            "status" => [
                "required",
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_LOCKED,
                ]),
            ],

            "locked_reason" => ["nullable", "string", "max:255"],
        ];
    }

    public function messages(): array
    {
        return [
            "full_name.required" => "Họ tên là bắt buộc.",
            "email.required" => "Email là bắt buộc.",
            "email.email" => "Email không hợp lệ.",
            "email.unique" => "Email đã tồn tại.",
            "password.required" => "Mật khẩu là bắt buộc.",
            "password.min" => "Mật khẩu phải có ít nhất 8 ký tự.",
            "role.required" => "Role là bắt buộc.",
            "role.in" => "Role không hợp lệ.",
            "status.required" => "Trạng thái là bắt buộc.",
            "status.in" => "Trạng thái không hợp lệ.",
        ];
    }
}
