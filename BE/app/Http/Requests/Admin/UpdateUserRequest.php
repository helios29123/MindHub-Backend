<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route("id");

        return [
            "full_name" => ["sometimes", "string", "max:255"],

            "email" => [
                "sometimes",
                "email",
                "max:255",
                Rule::unique("users", "email")
                    ->ignore($userId)
                    ->whereNull("deleted_at"),
            ],

            "password" => ["sometimes", "string", "min:8", "max:255"],

            "phone" => ["sometimes", "nullable", "string", "max:20"],

            "role" => [
                "sometimes",
                Rule::in([
                    User::ROLE_ADMIN,
                    User::ROLE_INSTRUCTOR,
                    User::ROLE_LEARNER,
                ]),
            ],

            "status" => [
                "sometimes",
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_LOCKED,
                ]),
            ],

            "locked_reason" => ["sometimes", "nullable", "string", "max:255"],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedFields = [
                "full_name",
                "email",
                "password",
                "phone",
                "role",
                "status",
                "locked_reason",
            ];

            $hasUpdateData = collect($allowedFields)->contains(
                fn(string $field): bool => $this->has($field),
            );

            if (!$hasUpdateData) {
                $validator
                    ->errors()
                    ->add(
                        "payload",
                        "Cần ít nhất một trường hợp lệ để cập nhật.",
                    );
            }
        });
    }

    public function messages(): array
    {
        return [
            "email.email" => "Email không hợp lệ.",
            "email.unique" => "Email đã tồn tại.",
            "password.min" => "Mật khẩu phải có ít nhất 8 ký tự.",
            "role.in" => "Role không hợp lệ.",
            "status.in" => "Trạng thái không hợp lệ.",
        ];
    }
}
