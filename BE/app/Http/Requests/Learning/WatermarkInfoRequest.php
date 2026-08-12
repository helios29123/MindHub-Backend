<?php
namespace App\Http\Requests\Learning;
use Illuminate\Foundation\Http\FormRequest;
final class WatermarkInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'lesson_id' => $this->route('lessonId'),
        ]);
    }
    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer', 'min:1'],
            'mode' => ['sometimes', 'nullable', 'string', 'in:static,moving'],
        ];
    }
    public function messages(): array
    {
        return [
            'lesson_id.required' => 'Bài học là bắt buộc.',
            'lesson_id.integer' => 'Bài học không hợp lệ.',
            'lesson_id.min' => 'Bài học không hợp lệ.',
            'mode.string' => 'Chế độ watermark không hợp lệ.',
            'mode.in' => 'Chế độ watermark chỉ hỗ trợ static hoặc moving.',
        ];
    }
}