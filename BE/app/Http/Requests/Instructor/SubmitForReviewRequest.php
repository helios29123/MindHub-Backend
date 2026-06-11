<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
final class SubmitForReviewRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
        ];
    }
    public function messages(): array
    {
        return [
            'id.required' => 'ID khóa học là bắt buộc.',
            'id.integer' => 'ID khóa học không hợp lệ.',
            'id.min' => 'ID khóa học không hợp lệ.',
        ];
    }
}