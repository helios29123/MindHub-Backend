<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
final class ListSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:active,expired,revoked,all'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
    public function messages(): array
    {
        return [
            'status.in' => 'Trạng thái phiên đăng nhập không hợp lệ.',
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'per_page.integer' => 'Số bản ghi mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số bản ghi mỗi trang phải lớn hơn hoặc bằng 1.',
            'per_page.max' => 'Số bản ghi mỗi trang không được vượt quá 50.',
        ];
    }
}