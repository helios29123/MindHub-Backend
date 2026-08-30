<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class DynamicAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('types') && is_string($this->types)) {
            $this->merge([
                'types' => explode(',', $this->types)
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
            'types' => ['nullable', 'array'],
            'types.*' => ['string', 'in:pending_order,inactive_learning'],
        ];
    }
}
