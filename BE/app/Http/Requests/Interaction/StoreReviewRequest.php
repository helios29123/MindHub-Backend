<?php
namespace App\Http\Requests\Interaction;
use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'course_id' => $this->route('id'),
        ]);
    }
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'min:1'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string', 'max:2000'],
        ];
    }
    public function messages(): array
    {
        return [
            'course_id.required' => 'Khóa học không hợp lệ.',
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.min' => 'Khóa học không hợp lệ.',
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên.',
            'rating.min' => 'Số sao đánh giá tối thiểu là 1.',
            'rating.max' => 'Số sao đánh giá tối đa là 5.',
            'content.string' => 'Nội dung đánh giá phải là chuỗi.',
            'content.max' => 'Nội dung đánh giá không được vượt quá 2000 ký tự.',
        ];
    }
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error('Dữ liệu đánh giá không hợp lệ.', $validator->errors()->toArray(), 422)
        );
    }
}