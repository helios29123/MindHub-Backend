<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
final class InstructorWithdrawalStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'user_id' => ['prohibited'],
            'instructor_id' => ['prohibited'],
            'status' => ['prohibited'],
            'requested_at' => ['prohibited'],
            'approved_at' => ['prohibited'],
            'paid_at' => ['prohibited'],
            'rejected_reason' => ['prohibited'],
            'account_number_snapshot' => ['prohibited'],
            'account_name_snapshot' => ['prohibited'],
            'payout_account_id' => ['required', 'integer', 'exists:payout_accounts,id'],
            'amount' => ['required', 'numeric', 'min:200000'],
            'note' => ['nullable', 'string', 'max:200'],
        ];
    }
    public function messages(): array
    {
        return [
            'user_id.prohibited' => 'Không được truyền user_id.',
            'instructor_id.prohibited' => 'Không được truyền instructor_id.',
            'status.prohibited' => 'Không được tự đặt trạng thái yêu cầu rút tiền.',
            'requested_at.prohibited' => 'Không được truyền thời gian gửi yêu cầu.',
            'approved_at.prohibited' => 'Không được truyền thời gian duyệt.',
            'paid_at.prohibited' => 'Không được truyền thời gian thanh toán.',
            'rejected_reason.prohibited' => 'Không được truyền lý do từ chối.',
            'account_number_snapshot.prohibited' => 'Không được truyền số tài khoản snapshot.',
            'account_name_snapshot.prohibited' => 'Không được truyền tên tài khoản snapshot.',
            'payout_account_id.required' => 'Vui lòng chọn tài khoản nhận tiền.',
            'payout_account_id.integer' => 'Tài khoản nhận tiền không hợp lệ.',
            'payout_account_id.exists' => 'Tài khoản nhận tiền không tồn tại.',
            'amount.required' => 'Vui lòng nhập số tiền rút.',
            'amount.numeric' => 'Số tiền rút phải là số.',
            'amount.min' => 'Số tiền rút tối thiểu là 200.000 đ.',
            'note.max' => 'Ghi chú không được vượt quá 200 ký tự.',
        ];
    }
}