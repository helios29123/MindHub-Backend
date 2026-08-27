<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\CommissionRule;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly CoursePurchaseGuardService $coursePurchaseGuardService
    ) {
    }

    public function createOrder(array $data, int $userId): object
    {
        $courseId = (int) ($data['course_id'] ?? 0);

        if ($courseId <= 0) {
            throw new BusinessException('Thiếu mã khóa học.', 422);
        }

        return DB::transaction(function () use ($courseId, $userId): object {
            $course = $this->coursePurchaseGuardService->assertCanBuyCourse($userId, $courseId);

            $pendingOrder = DB::table('orders')
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->where('status', Order::STATUS_PENDING_PAYMENT)
                ->where('payment_status', Order::PAYMENT_PENDING)
                ->lockForUpdate()
                ->first();

            if ($pendingOrder) {
                return $pendingOrder;
            }

            $activeCommissionRule = CommissionRule::query()
                ->where('is_active', 1)
                ->first();

            if (! $activeCommissionRule) {
                throw new BusinessException('Không tìm thấy luật hoa hồng đang áp dụng. Vui lòng liên hệ Admin.', 500);
            }

            $amount = $this->resolveCoursePrice($course);

            $orderId = DB::table('orders')->insertGetId([
                'order_code' => $this->generateOrderCode(),
                'user_id' => $userId,
                'course_id' => $courseId,
                'coupon_id' => null,
                'commission_rule_id' => $activeCommissionRule->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'payment_status' => Order::PAYMENT_PENDING,
                'price_snapshot' => $amount,
                'discount_amount' => 0,
                'amount' => $amount,
                'payment_method' => null,
                'provider_transaction_id' => null,
                'paid_at' => null,
                'expires_at' => now()->addHours(max(1, (int) config('mindhub.pending_order_expire_hours', 24))),
                'cancelled_reason' => null,
                'failed_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('orders')->where('id', $orderId)->first();
        });
    }

    public function showUserOrder(int $orderId, int $userId): object
    {
        $order = DB::table('orders')->where('id', $orderId)->where('user_id', $userId)->first();
        if (! $order) {
            throw new BusinessException('Không tìm thấy đơn hàng.', 404);
        }
        return $order;
    }

    public function getMyOrders(int $userId, array $filters = []): array
    {
        $query = DB::table('orders')->where('user_id', $userId)->orderByDesc('id');
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        $perPage = max(1, min((int) ($filters['per_page'] ?? 15), 100));
        return $query->paginate($perPage)->toArray();
    }

    public function cancelUserOrder(int $orderId, int $userId): object
    {
        return DB::transaction(function () use ($orderId, $userId): object {
            $order = DB::table('orders')->where('id', $orderId)->where('user_id', $userId)->lockForUpdate()->first();
            if (! $order) {
                throw new BusinessException('Không tìm thấy đơn hàng.', 404);
            }
            if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
                throw new BusinessException('Chỉ được hủy đơn hàng đang chờ thanh toán.', 409);
            }
            if ($order->payment_status !== Order::PAYMENT_PENDING) {
                throw new BusinessException('Đơn hàng không còn ở trạng thái có thể hủy.', 409);
            }
            DB::table('orders')->where('id', $orderId)->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_reason' => 'Người dùng hủy đơn hàng.',
                'updated_at' => now(),
            ]);
            return DB::table('orders')->where('id', $orderId)->first();
        });
    }

    private function resolveCoursePrice(object $course): float
    {
        $salePrice = $course->sale_price ?? null;
        if ($salePrice !== null && (float) $salePrice > 0) {
            return (float) $salePrice;
        }
        return (float) ($course->price ?? 0);
    }

    private function generateOrderCode(): string
    {
        return 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
    }
}
