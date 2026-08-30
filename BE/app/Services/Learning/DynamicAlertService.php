<?php

namespace App\Services\Learning;

use App\Repositories\Learning\DynamicAlertRepository;
use Illuminate\Support\Collection;

class DynamicAlertService
{
    public function __construct(
        private readonly DynamicAlertRepository $repository
    ) {
    }

    public function getDynamicAlerts(int $learnerId, array $filters): array
    {
        $alerts = new Collection();
        $types = $filters['types'] ?? ['pending_order', 'inactive_learning'];

        if (in_array('pending_order', $types)) {
            $pendingOrders = $this->repository->getPendingOrders($learnerId);
            foreach ($pendingOrders as $order) {
                $alerts->push([
                    'type' => 'pending_order',
                    'title' => 'Bạn có đơn hàng chưa thanh toán',
                    'message' => 'Đơn hàng #' . $order->id . ' của bạn đang chờ thanh toán. Vui lòng thanh toán để bắt đầu học.',
                    'action_url' => '/orders/' . $order->id,
                    'severity' => 'warning',
                    'created_at' => $order->created_at ? $order->created_at->toIso8601String() : now()->toIso8601String(),
                ]);
            }
        }



        if (in_array('inactive_learning', $types)) {
            $inactiveEnrollments = $this->repository->getInactiveEnrollments($learnerId, 14);
            foreach ($inactiveEnrollments as $enrollment) {
                $alerts->push([
                    'type' => 'inactive_learning',
                    'title' => 'Bạn đã lâu chưa học',
                    'message' => 'Hãy quay lại tiếp tục khóa học để duy trì tiến độ học tập của bạn.',
                    'action_url' => '/learn/courses/' . $enrollment->course_id, // Adjust URL to your frontend's route
                    'severity' => 'info',
                    'created_at' => $enrollment->last_accessed_at ? $enrollment->last_accessed_at->toIso8601String() : now()->toIso8601String(),
                ]);
            }
        }

        // Sort alerts by created_at descending
        $sortedAlerts = $alerts->sortByDesc('created_at')->values();

        $limit = (int) ($filters['limit'] ?? 10);

        return $sortedAlerts->take($limit)->all();
    }
}
