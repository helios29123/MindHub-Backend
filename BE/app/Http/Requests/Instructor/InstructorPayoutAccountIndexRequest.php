<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
final class InstructorPayoutAccountIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:active,inactive,verified,disabled,pending_verification'],
        ];
    }
    public function messages(): array
    {
        return [
            'status.in' => 'Trạng thái tài khoản nhận tiền không hợp lệ.',
        ];
    }
}