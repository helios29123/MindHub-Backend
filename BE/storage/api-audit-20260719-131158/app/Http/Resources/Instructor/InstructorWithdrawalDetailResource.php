<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
final class InstructorWithdrawalDetailResource extends InstructorWithdrawalResource
{
    public function toArray(Request $request): array
    {
        $base = parent::toArray($request);
        $base['account']['provider'] = $this->payoutAccount?->provider;
        $base['timeline'] = $this->timeline();
        $base['note'] = 'Lịch sử rút tiền chỉ ghi nhận yêu cầu withdraw_requests, không trộn với báo cáo doanh thu.';
        return $base;
    }
    private function timeline(): array
    {
        $status = (string) $this->status;
        $timeline = [
            [
                'key' => 'requested',
                'label' => 'Đã gửi yêu cầu',
                'completed' => $this->requested_at !== null,
                'time' => $this->formatDate($this->requested_at),
            ],
            [
                'key' => 'approved',
                'label' => 'Đã duyệt',
                'completed' => $this->approved_at !== null || in_array($status, ['approved', 'paid'], true),
                'time' => $this->formatDate($this->approved_at),
            ],
            [
                'key' => 'paid',
                'label' => 'Đã thanh toán',
                'completed' => $this->paid_at !== null || $status === 'paid',
                'time' => $this->formatDate($this->paid_at),
            ],
        ];
        if ($status === 'rejected') {
            $timeline[] = [
                'key' => 'rejected',
                'label' => 'Bị từ chối',
                'completed' => true,
                'time' => $this->formatDate($this->updated_at),
            ];
        }
        if ($status === 'cancelled') {
            $timeline[] = [
                'key' => 'cancelled',
                'label' => 'Đã hủy',
                'completed' => true,
                'time' => $this->formatDate($this->updated_at),
            ];
        }
        return $timeline;
    }
}