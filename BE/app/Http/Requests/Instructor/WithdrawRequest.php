<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'payout_account_id' => ['required', 'integer', 'exists:payout_accounts,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Số tiền là bắt buộc.',
            'amount.numeric' => 'Số tiền phải là số.',
            'amount.min' => 'Số tiền phải lớn hơn hoặc bằng 1.',
            'payout_account_id.required' => 'Tài khoản nhận tiền là bắt buộc.',
            'payout_account_id.integer' => 'Tài khoản nhận tiền không hợp lệ.',
            'payout_account_id.exists' => 'Tài khoản nhận tiền không tồn tại.',
            'note.string' => 'Ghi chú phải là chuỗi.',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự.',
        ];
    }
}