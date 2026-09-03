<?php

namespace App\Http\Requests\Learning;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SaveVideoProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $current = $this->input('current_second');
        $duration = $this->input('duration_second');

        $merge = [];
        if ($current !== null && is_numeric($current)) {
            $merge['current_second'] = (int) round((float) $current);
        }
        if ($duration !== null && is_numeric($duration)) {
            $merge['duration_second'] = (int) round((float) $duration);
        }
        if ($this->input('force_date') === '' || $this->input('force_date') === null) {
            $merge['force_date'] = null;
        }
        if ($this->input('timezone') === '' || $this->input('timezone') === null) {
            $merge['timezone'] = null;
        }
        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'current_second' => ['required', 'integer', 'min:0'],
            'duration_second' => ['nullable', 'integer', 'min:1'],
            'is_completed' => ['nullable', 'boolean'],
            'force_date' => ['nullable', 'string'],
            'timezone' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_second.required' => 'Dữ liệu không hợp lệ.',
            'current_second.integer' => 'Dữ liệu không hợp lệ.',
            'current_second.min' => 'Dữ liệu không hợp lệ.',
            'duration_second.integer' => 'Dữ liệu không hợp lệ.',
            'duration_second.min' => 'Dữ liệu không hợp lệ.',
            'is_completed.boolean' => 'Dữ liệu không hợp lệ.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Dữ liệu không hợp lệ.',
                $validator->errors()->toArray(),
                422
            )
        );
    }
}
