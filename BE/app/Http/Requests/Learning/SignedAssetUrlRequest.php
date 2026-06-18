<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class SignedAssetUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ttl_seconds' => ['nullable', 'integer', 'min:60', 'max:900'],
        ];
    }
}
