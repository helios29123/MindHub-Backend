<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class UpdateMeRequest extends FormRequest
{
    private const SUPPORTED_FIELDS = [
        'full_name',
        'phone',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $unsupportedFields = array_diff(
            array_keys($this->all()),
            self::SUPPORTED_FIELDS
        );

        if (! empty($unsupportedFields)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Field không được hỗ trợ trong Sprint 1.',
                'errors' => [
                    'unsupported_fields' => array_values($unsupportedFields),
                ],
            ], 422));
        }

        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.string' => 'Họ tên phải là chuỗi.',
            'full_name.max' => 'Họ tên không được vượt quá 255 ký tự.',
            'phone.string' => 'Số điện thoại phải là chuỗi.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
            'errors' => $validator->errors(),
        ], 422));
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
            'errors' => [],
        ], 403));
    }
}