<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class MarkOrderPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return ['transaction_code' => ['nullable', 'string', 'max:255'], 'paid_at' => ['nullable', 'date'], 'reason' => ['nullable', 'string', 'max:1000']];
    }
}
