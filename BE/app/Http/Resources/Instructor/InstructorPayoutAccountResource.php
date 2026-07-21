<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorPayoutAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'provider' => $this->provider,
            'provider_label' => $this->providerLabel($this->provider),
            'account_name' => $this->account_name,
            'account_number_masked' => $this->maskAccountNumber($this->account_number),
            'status' => $this->status,
            'status_label' => $this->statusLabel($this->status),
            'is_default' => (bool) $this->is_default,
        ];
    }
    private function providerLabel(?string $provider): string
    {
        return match ($provider) {
            'bank' => 'Ngân hàng',
            'momo' => 'MoMo',
            'zalopay' => 'ZaloPay',
            default => $provider ?: 'Không xác định',
        };
    }
    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'active' => 'Đang hoạt động',
            'inactive' => 'Đã tắt',
            'pending' => 'Chờ xác minh',
            'rejected' => 'Bị từ chối',
            default => 'Không xác định',
        };
    }
    private function maskAccountNumber(?string $accountNumber): ?string
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
}