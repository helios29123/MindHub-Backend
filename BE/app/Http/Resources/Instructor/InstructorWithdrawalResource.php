<?php
namespace App\Http\Resources\Instructor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class InstructorWithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $bankName = $this->bank_name ?: ($this->payoutAccount->bank_name ?? 'Techcombank');
        $accountName = $this->account_name_snapshot ?: ($this->payoutAccount->account_name ?? '');
        $accountNumber = $this->account_number_snapshot ?: ($this->payoutAccount->account_number ?? '');

        return [
            'id' => (int) $this->id,
            'code' => $this->displayCode((int) $this->id),
            'display_code' => $this->displayCode((int) $this->id),
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'bank_name' => $bankName,
            'account_name_snapshot' => $accountName,
            'account_number_snapshot' => $accountNumber,
            'account_number_masked' => $this->maskAccountNumber($accountNumber),
            'account' => [
                'bank_name' => $bankName,
                'account_name' => $accountName,
                'account_name_snapshot' => $accountName,
                'account_number_snapshot_masked' => $this->maskAccountNumber($accountNumber),
            ],
            'status' => $this->status,
            'status_label' => $this->statusLabel($this->status),
            'requested_at' => $this->formatDate($this->requested_at),
            'approved_at' => $this->formatDate($this->approved_at),
            'paid_at' => $this->formatDate($this->paid_at),
            'rejected_reason' => $this->rejected_reason,
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at),
        ];
    }
    protected function displayCode(int $id): string
    {
        return '#WR-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
    }
    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã duyệt',
            'rejected' => 'Bị từ chối',
            'paid' => 'Đã thanh toán',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định',
        };
    }
    protected function maskAccountNumber(?string $accountNumber): ?string
    {
        if ($accountNumber === null || $accountNumber === '') {
            return null;
        }
        $length = mb_strlen($accountNumber);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        return str_repeat('*', max(0, $length - 4)) . mb_substr($accountNumber, -4);
    }
    protected function formatDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}