<?php
namespace App\Http\Requests\Learning;
use Illuminate\Foundation\Http\FormRequest;
final class SignedLessonVideoUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'lesson_id' => $this->route('id'),
        ]);
    }
    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer', 'min:1'],
            'ttl_seconds' => ['sometimes', 'nullable', 'integer', 'min:60', 'max:600'],
        ];
    }
    public function messages(): array
    {
        return [
            'lesson_id.required' => 'Bài học là bắt buộc.',
            'lesson_id.integer' => 'Bài học không hợp lệ.',
            'lesson_id.min' => 'Bài học không hợp lệ.',
            'ttl_seconds.integer' => 'Thời hạn link xem video phải là số nguyên.',
            'ttl_seconds.min' => 'Thời hạn link xem video tối thiểu là 60 giây.',
            'ttl_seconds.max' => 'Thời hạn link xem video tối đa là 600 giây.',
        ];
    }
}