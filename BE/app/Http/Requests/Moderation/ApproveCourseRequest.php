<?php
namespace App\Http\Requests\Moderation;
use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class ApproveCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1', 'exists:courses,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'id.required' => 'Dữ liệu không hợp lệ.',
            'id.integer' => 'Dữ liệu không hợp lệ.',
            'id.min' => 'Dữ liệu không hợp lệ.',
            'id.exists' => 'Không tìm thấy dữ liệu.',
        ];
    }
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();
        $status = $errors->has('id') && in_array('Không tìm thấy dữ liệu.', $errors->get('id'), true)
            ? 404
            : 422;
        $message = $status === 404
            ? 'Không tìm thấy dữ liệu.'
            : 'Dữ liệu không hợp lệ.';
        throw new HttpResponseException(
            ApiResponse::error($message, $errors->toArray(), $status)
        );
    }
}