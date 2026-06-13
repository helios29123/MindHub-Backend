<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Vui lﾃｲng ch盻肱 khﾃｳa h盻皇.',
            'course_id.exists' => 'Khﾃｳa h盻皇 khﾃｴng t盻渡 t蘯｡i.',
        ];
    }
}
