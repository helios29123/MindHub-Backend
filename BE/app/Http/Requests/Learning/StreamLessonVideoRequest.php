<?php
namespace App\Http\Requests\Learning;
use Illuminate\Foundation\Http\FormRequest;
final class StreamLessonVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route stream không dùng auth.session (thẻ <video> không gửi Bearer được).
        // Bảo mật dựa trên URL đã ký (middleware 'signed') + kiểm tra enrollment trong service.
        return true;
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
            'u' => ['sometimes', 'nullable', 'integer', 'min:1'],
            's' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
    public function messages(): array
    {
        return [
            'lesson_id.required' => 'Bài học là bắt buộc.',
            'lesson_id.integer' => 'Bài học không hợp lệ.',
            'lesson_id.min' => 'Bài học không hợp lệ.',
            'u.integer' => 'Thông tin người dùng trong link không hợp lệ.',
            's.integer' => 'Thông tin phiên đăng nhập trong link không hợp lệ.',
        ];
    }
}