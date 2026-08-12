<?php
namespace App\Http\Requests\Wishlist;
use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
final class DestroyWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'courseId' => $this->route('courseId'),
        ]);
    }
    public function rules(): array
    {
        return [
            'courseId' => ['required', 'integer', 'min:1'],
        ];
    }
    public function messages(): array
    {
        return [
            'courseId.required' => 'courseId không hợp lệ.',
            'courseId.integer' => 'courseId không hợp lệ.',
            'courseId.min' => 'courseId không hợp lệ.',
        ];
    }
    public function validatedCourseId(): int
    {
        return (int) $this->validated('courseId');
    }
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'courseId không hợp lệ.',
                $validator->errors()->toArray(),
                422
            )
        );
    }
}