<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUserActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:lock,unlock,activate,deactivate'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'locked_reason' => ['required_if:action,lock', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Hành động không được để trống.',
            'action.in' => 'Hành động xử lý hàng loạt không hợp lệ.',
            'user_ids.required' => 'Danh sách tài khoản được chọn không được để trống.',
            'user_ids.array' => 'Danh sách tài khoản phải là một mảng.',
            'user_ids.min' => 'Vui lòng chọn ít nhất một tài khoản.',
            'user_ids.*.exists' => 'Tài khoản được chọn không tồn tại trong hệ thống.',
            'locked_reason.required_if' => 'Lý do khóa tài khoản là bắt buộc khi thực hiện khóa hàng loạt.',
            'locked_reason.max' => 'Lý do khóa không được vượt quá 1000 ký tự.',
        ];
    }
}
