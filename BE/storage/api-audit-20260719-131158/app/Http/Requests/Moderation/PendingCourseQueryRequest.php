<?php
namespace App\Http\Requests\Moderation;
use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class PendingCourseQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'string', Rule::in([
                'newest',
                'oldest',
                'title_asc',
                'title_desc',
            ])],
        ];
    }
    public function messages(): array
    {
        return [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'per_page.integer' => 'Số bản ghi mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số bản ghi mỗi trang phải lớn hơn hoặc bằng 1.',
            'per_page.max' => 'Số bản ghi mỗi trang không được vượt quá 100.',
            'search.string' => 'Từ khóa tìm kiếm không hợp lệ.',
            'search.max' => 'Từ khóa tìm kiếm không được vượt quá 255 ký tự.',
            'sort.in' => 'Kiểu sắp xếp không hợp lệ.',
        ];
    }
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error('Tham số không hợp lệ.', $validator->errors()->toArray(), 422)
        );
    }
}