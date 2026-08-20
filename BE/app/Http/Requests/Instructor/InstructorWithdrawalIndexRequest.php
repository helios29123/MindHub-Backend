<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
final class InstructorWithdrawalIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'in:all,automatic_payout,early_withdrawal,automatic,early'],
            'status' => ['nullable', 'string', 'in:all,pending,approved,processing,manual_required,paid,rejected,cancelled,failed'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
    public function messages(): array
    {
        return [
            'status.in' => 'Trạng thái yêu cầu rút tiền không hợp lệ.',
            'date_from.date' => 'Ngày bắt đầu không hợp lệ.',
            'date_to.date' => 'Ngày kết thúc không hợp lệ.',
            'date_to.after_or_equal' => 'Ngày kết thúc không được trước ngày bắt đầu.',
            'page.integer' => 'Trang không hợp lệ.',
            'page.min' => 'Trang phải lớn hơn 0.',
            'per_page.integer' => 'Số dòng mỗi trang không hợp lệ.',
            'per_page.min' => 'Số dòng mỗi trang phải lớn hơn 0.',
            'per_page.max' => 'Số dòng mỗi trang không được vượt quá 50.',
        ];
    }
}