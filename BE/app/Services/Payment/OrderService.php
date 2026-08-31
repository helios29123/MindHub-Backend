<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\CommissionRule;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Repositories\Payment\CouponRepository;
use App\Services\Marketing\CouponPricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly CoursePurchaseGuardService $coursePurchaseGuardService,
        private readonly CouponRepository $couponRepository,
        private readonly CouponPricingService $couponPricing
    ) {
    }

    public function createOrder(array $data, int $userId): object
    {
        $courseId = (int) ($data['course_id'] ?? 0);

        if ($courseId <= 0) {
            throw new BusinessException('Thiếu mã khóa học.', 422);
        }

        return DB::transaction(function () use ($courseId, $userId): object {
            $this->coursePurchaseGuardService->assertCanBuyCourse($userId, $courseId);

            /** @var Course|null $course */
            $course = Course::query()
                ->whereKey($courseId)
                ->lockForUpdate()
                ->first();

            if (! $course) {
                throw new BusinessException('Không tìm thấy khóa học.', 404);
            }

            // Pending order đã snapshot thì giữ nguyên giá/coupon cũ.
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

            $commissionRule = CommissionRule::query()
                ->where('is_active', 1)
                ->lockForUpdate()
                ->first();

            if (! $commissionRule) {
                throw new BusinessException(
                    'Không tìm thấy luật hoa hồng đang áp dụng. Vui lòng liên hệ Admin.',
                    500
                );
            }

            /** @var Coupon|null $coupon */
            $coupon = $this->couponRepository->lockCurrentForCourse($courseId);

            $quote = $this->couponPricing->quote($course, $coupon);

            // Đồng bộ cache hiển thị sale_price, nhưng snapshot Order luôn lấy quote realtime này.
            $course->forceFill(['sale_price' => $quote['sale_price']])->saveQuietly();

            if (($quote['campaign_type'] ?? null) === Coupon::CAMPAIGN_TRIAL) {
                if (! $coupon) {
                    throw new BusinessException('Campaign học thử không hợp lệ.', 409);
                }

                return $this->createTrialOrder(
                    $course,
                    $coupon,
                    (int) $commissionRule->id,
                    $userId,
                    $quote
                );
            }

            $amount = (int) $quote['sale_price'];
            $minimumPayable = (int) config('order.minimum_payable_amount', 10000);

            if ($amount > 0 && $amount < $minimumPayable) {
                throw new BusinessException(
                    "Giá sau giảm phải là 0đ hoặc từ {$minimumPayable}đ trở lên.",
                    422
                );
            }

            if ($amount <= 0) {
                throw new BusinessException(
                    'Giá 0đ chỉ hợp lệ với campaign_type=trial.',
                    409
                );
            }

            $orderId = DB::table('orders')->insertGetId([
                'order_code' => $this->generateOrderCode(),
                'user_id' => $userId,
                'course_id' => $courseId,
                'coupon_id' => $coupon?->id,
                'commission_rule_id' => $commissionRule->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'payment_status' => Order::PAYMENT_PENDING,
                'price_snapshot' => (int) $quote['price'],
                'discount_amount' => (int) $quote['discount_amount'],
                'amount' => $amount,
                'payment_method' => null,
                'provider_transaction_id' => null,
                'paid_at' => null,
                'expires_at' => now()->addHours(max(1, (int) config('order.pending_expire_hours', 24))),
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
        $order = DB::table('orders')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();

        if (! $order) {
            throw new BusinessException('Không tìm thấy đơn hàng.', 404);
        }

        return $order;
    }

    public function getMyOrders(int $userId, array $filters = []): array
    {
        $query = DB::table('orders')
            ->where('user_id', $userId)
            ->orderByDesc('id');

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
            $order = DB::table('orders')
                ->where('id', $orderId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

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

    private function createTrialOrder(
        Course $course,
        Coupon $coupon,
        int $commissionRuleId,
        int $userId,
        array $quote
    ): object {
        // Idempotency + "mỗi learner chỉ trial course 1 lần":
        // request lặp lại trả lại trial order cũ, không cấp thêm quyền/không tăng used_count.
        $existingTrialOrder = DB::table('orders')
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->where('status', Order::STATUS_PAID)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->where('amount', 0)
            ->where('payment_method', 'coupon_trial')
            ->lockForUpdate()
            ->first();

        if ($existingTrialOrder) {
            return $existingTrialOrder;
        }

        $existingEnrollment = Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->lockForUpdate()
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->expires_at === null) {
                throw new BusinessException('Bạn đã sở hữu khóa học này.', 409);
            }

            throw new BusinessException('Bạn đã từng học thử khóa học này.', 409);
        }

        if (
            $coupon->usage_limit === null
            || (int) $coupon->usage_limit < 1
            || (int) $coupon->usage_limit > (int) config('coupon.trial_max_uses', 15)
        ) {
            throw new BusinessException('Giới hạn lượt học thử không hợp lệ.', 409);
        }

        if ((int) $coupon->used_count >= (int) $coupon->usage_limit) {
            $coupon->forceFill(['status' => Coupon::STATUS_USED_UP])->save();
            $this->couponPricing->syncCourseSalePrice($course->refresh());
            throw new BusinessException('Campaign học thử đã hết lượt.', 409);
        }

        $orderId = DB::table('orders')->insertGetId([
            'order_code' => $this->generateOrderCode(),
            'user_id' => $userId,
            'course_id' => $course->id,
            'coupon_id' => $coupon->id,
            'commission_rule_id' => $commissionRuleId,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'price_snapshot' => (int) $quote['price'],
            'discount_amount' => (int) $quote['price'],
            'amount' => 0,
            'payment_method' => 'coupon_trial',
            'provider_transaction_id' => null,
            'paid_at' => now(),
            'expires_at' => null,
            'cancelled_reason' => null,
            'failed_reason' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Enrollment::query()->create([
            'user_id' => $userId,
            'course_id' => $course->id,
            'order_id' => $orderId,
            'status' => Enrollment::STATUS_ACTIVE,
            'progress_percent' => 0,
            'enrolled_at' => now(),
            'expires_at' => now()->addDays((int) config('coupon.trial_access_days', 7)),
            'completed_at' => null,
            'last_accessed_at' => null,
        ]);

        $nextUsedCount = (int) $coupon->used_count + 1;
        $nextStatus = $nextUsedCount >= (int) $coupon->usage_limit
            ? Coupon::STATUS_USED_UP
            : Coupon::STATUS_ACTIVE;

        $coupon->forceFill([
            'used_count' => $nextUsedCount,
            'status' => $nextStatus,
        ])->save();

        DB::table('wishlist')
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->delete();

        // Nếu lượt vừa rồi làm used_up thì sale_price phải quay lại price ngay.
        $this->couponPricing->syncCourseSalePrice($course->refresh());

        return DB::table('orders')->where('id', $orderId)->first();
    }

    private function generateOrderCode(): string
    {
        for ($i = 0; $i < 20; $i++) {
            $code = 'ORD-' . now()->format('dmy') . '-' . strtoupper(Str::random(6));
            if (! DB::table('orders')->where('order_code', $code)->exists()) {
                return $code;
            }
        }

        throw new BusinessException('Không thể sinh mã đơn hàng duy nhất, vui lòng thử lại.', 500);
    }
}
