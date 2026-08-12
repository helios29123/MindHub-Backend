<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class MarkPayoutItemPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return ['transaction_code' => ['nullable', 'string', 'max:255'], 'paid_at' => ['nullable', 'date'], 'note' => ['nullable', 'string', 'max:1000'], 'reason' => ['nullable', 'string', 'max:1000']];
    }
}
