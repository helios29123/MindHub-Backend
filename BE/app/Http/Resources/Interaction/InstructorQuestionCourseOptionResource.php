<?php
namespace App\Http\Resources\Interaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorQuestionCourseOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = data_get($this->resource, 'status');
        return [
            'id' => (int) data_get($this->resource, 'id'),
            'title' => data_get($this->resource, 'title'),
            'status' => $status,
            'status_label' => $this->statusLabel($status),
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