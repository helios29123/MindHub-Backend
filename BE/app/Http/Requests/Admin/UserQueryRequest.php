<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "page" => ["nullable", "integer", "min:1"],
            "per_page" => ["nullable", "integer", "min:1", "max:100"],
            "search" => ["nullable", "string", "max:255"],

            "role" => [
                "nullable",
                Rule::in([
                    User::ROLE_ADMIN,
                    User::ROLE_INSTRUCTOR,
                    User::ROLE_LEARNER,
                ]),
            ],

            "status" => [
                "nullable",
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_LOCKED,
                ]),
            ],

            "sort_by" => [
                "nullable",
                Rule::in([
                    "id",
                    "full_name",
                    "email",
                    "role",
                    "status",
                    "created_at",
                    "last_login_at",
                ]),
            ],

            "sort_direction" => ["nullable", Rule::in(["asc", "desc"])],
        ];
    }

    public function messages(): array
    {
        return [
            "role.in" => "Role không hợp lệ.",
            "status.in" => "Trạng thái không hợp lệ.",
            "sort_by.in" => "Trường sắp xếp không hợp lệ.",
            "sort_direction.in" => "Chiều sắp xếp không hợp lệ.",
        ];
    }
}
