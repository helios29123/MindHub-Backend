<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
final class LogoutAllRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'keep_current' => ['nullable', 'boolean'],
        ];
    }
    public function messages(): array
    {
        return [
            'keep_current.boolean' => 'Giá trị giữ phiên hiện tại không hợp lệ.',
        ];
    }
}