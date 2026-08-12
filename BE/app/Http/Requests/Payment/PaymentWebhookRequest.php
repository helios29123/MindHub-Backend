<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'transaction_code' => ['nullable', 'string', 'max:255'],
            'payment_status' => ['required', 'in:paid,failed'],
            'paid_at' => ['required_if:payment_status,paid', 'nullable', 'date'],
        ];
    }
}
