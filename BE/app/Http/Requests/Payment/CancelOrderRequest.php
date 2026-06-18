<?php
namespace App\Http\Requests\Payment;
use Illuminate\Foundation\Http\FormRequest;
class CancelOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'orderId' => $this->route('orderId'),
        ]);
    }
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'orderId' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
    public function messages(): array
    {
        return [
            'orderId.required' => 'Mã đơn hàng là bắt buộc.',
            'orderId.integer' => 'Mã đơn hàng không hợp lệ.',
            'orderId.min' => 'Mã đơn hàng không hợp lệ.',
            'reason.string' => 'Lý do hủy đơn hàng không hợp lệ.',
            'reason.max' => 'Lý do hủy đơn hàng không được vượt quá 255 ký tự.',
        ];
    }
}