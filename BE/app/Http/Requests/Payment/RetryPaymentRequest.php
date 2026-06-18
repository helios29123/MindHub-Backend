<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RetryPaymentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_id' => $this->route('orderId'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'min:1'],
            'payment_method' => [
                'nullable',
                'string',
                Rule::in(['vnpay', 'momo', 'bank_transfer']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Mã đơn hàng là bắt buộc.',
            'order_id.integer' => 'Mã đơn hàng không hợp lệ.',
            'order_id.min' => 'Mã đơn hàng không hợp lệ.',
            'payment_method.string' => 'Phương thức thanh toán không hợp lệ.',
            'payment_method.in' => 'Phương thức thanh toán không được hỗ trợ.',
        ];
    }
}