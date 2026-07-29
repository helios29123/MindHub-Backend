<?php

namespace App\Http\Requests\Instructor;

use App\Http\Requests\BaseApiRequest;

class StoreInstructorUpgradeRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:20'],

            'bio' => ['required', 'string', 'min:30'],
            'expertise' => ['required', 'string', 'max:2000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'level' => ['required', 'string', 'max:50'],

            'bank_provider' => ['required', 'string', 'max:50'],
            'bank_account_number' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',

            'bio.required' => 'Mô tả bản thân không được để trống.',
            'bio.min' => 'Mô tả bản thân cần ít nhất 30 ký tự để admin có cơ sở duyệt giảng viên.',

            'expertise.required' => 'Chuyên môn giảng dạy không được để trống.',
            'expertise.max' => 'Chuyên môn giảng dạy không được vượt quá 2000 ký tự.',

            'experience_years.required' => 'Số năm kinh nghiệm không được để trống.',
            'experience_years.integer' => 'Số năm kinh nghiệm phải là số nguyên.',
            'experience_years.min' => 'Số năm kinh nghiệm không hợp lệ.',
            'experience_years.max' => 'Số năm kinh nghiệm không hợp lệ.',

            'level.required' => 'Cấp độ giảng viên không được để trống.',

            'bank_provider.required' => 'Tên ngân hàng/nhà cung cấp thanh toán không được để trống.',
            'bank_account_number.required' => 'Số tài khoản ngân hàng không được để trống.',
            'bank_account_name.required' => 'Tên chủ tài khoản ngân hàng không được để trống.',
        ];
    }
}
