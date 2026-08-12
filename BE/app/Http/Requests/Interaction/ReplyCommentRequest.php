<?php
namespace App\Http\Requests\Interaction;
use Illuminate\Foundation\Http\FormRequest;
final class ReplyCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }
    public function messages(): array
    {
        return [
            'content.required' => 'Vui lòng nhập nội dung trả lời.',
            'content.string' => 'Nội dung trả lời không hợp lệ.',
            'content.min' => 'Nội dung trả lời không được để trống.',
            'content.max' => 'Nội dung trả lời không được vượt quá 2000 ký tự.',
        ];
    }
}