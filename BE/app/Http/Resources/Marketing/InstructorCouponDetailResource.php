<?php
namespace App\Http\Resources\Marketing;
use Illuminate\Http\Request;
final class InstructorCouponDetailResource extends InstructorCouponResource
{
    public function toArray(Request $request): array
    {
        $base = parent::toArray($request);
        $usageLimit = $base['usage_limit'];
        $usedCount = $base['used_count'];
        $base['usage'] = [
            'used_count' => $usedCount,
            'usage_limit' => $usageLimit,
            'remaining_usage' => $base['remaining_usage'],
            'usage_label' => $base['usage_label'],
        ];
        $base['validity'] = [
            'start_at' => $base['start_at'],
            'end_at' => $base['end_at'],
            'validity_label' => $this->validityLabel($base['start_at'], $base['end_at']),
        ];
        $base['note'] = 'Doanh thu giảng viên được tính trên số tiền học viên thực trả sau giảm giá.';
        return $base;
    }
    private function validityLabel(?string $startAt, ?string $endAt): string
    {
        if ($startAt === null && $endAt === null) {
            return 'Không giới hạn thời gian';
        }
        if ($startAt === null) {
            return 'Đến ' . $endAt;
        }
        if ($endAt === null) {
            return 'Từ ' . $startAt;
        }
        return $startAt . ' - ' . $endAt;
    }
}