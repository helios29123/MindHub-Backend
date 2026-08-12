<?php
namespace App\Http\Resources\Marketing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorCouponCourseOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'status_label' => $this->statusLabel($this->status),
        ];
    }
    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'Bản nháp',
            'pending_review' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Bị từ chối',
            'published' => 'Đang công khai',
            'hidden' => 'Đang ẩn',
            default => 'Không xác định',
        };
    }
}