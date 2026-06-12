<?php
namespace App\Http\Requests\Wishlist;
use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
final class WishlistQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
    public function messages(): array
    {
        return [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'per_page.integer' => 'Số lượng mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số lượng mỗi trang phải lớn hơn hoặc bằng 1.',
            'per_page.max' => 'Số lượng mỗi trang không được vượt quá 50.',
        ];
    }
    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? 10);
    }
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Tham số phân trang không hợp lệ.',
                $validator->errors()->toArray(),
                422
            )
        );
    }
}