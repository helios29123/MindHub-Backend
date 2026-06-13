<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
final class TogglePreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'is_preview' => ['required', 'boolean'],
        ];
    }
    public function messages(): array
    {
        return [
            'is_preview.required' => 'Vui lòng chọn trạng thái preview.',
            'is_preview.boolean' => 'Trạng thái preview không hợp lệ.',
        ];
    }
}