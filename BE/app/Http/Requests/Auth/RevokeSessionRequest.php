<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
final class RevokeSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'session_id' => $this->route('sessionId'),
        ]);
    }
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'integer', 'min:1'],
        ];
    }
    public function messages(): array
    {
        return [
            'session_id.required' => 'Phiên đăng nhập là bắt buộc.',
            'session_id.integer' => 'Phiên đăng nhập không hợp lệ.',
            'session_id.min' => 'Phiên đăng nhập không hợp lệ.',
        ];
    }
}