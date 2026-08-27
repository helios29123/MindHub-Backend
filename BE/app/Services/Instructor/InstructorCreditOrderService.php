<?php

namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Models\CourseCreditPackage;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstructorCreditOrderService
{
    public function createOrder(int $instructorId, int $packageId): Order
    {
        return DB::transaction(function () use ($instructorId, $packageId): Order {
            $user = DB::table('users')
                ->first();

            if (! $user) {
                throw new BusinessException('Không tìm thấy người dùng.', 404);
            }

            if (($user->role ?? null) !== 'instructor') {
                throw new BusinessException('Chỉ giảng viên mới được mua gói lượt tạo khóa học.', 403);
            }

            if (($user->status ?? null) !== 'active' || (int) ($user->locked ?? 0) === 1) {
                throw new BusinessException('Tài khoản giảng viên không được phép mua gói lượt.', 403);
            }

            $package = CourseCreditPackage::query()
                ->where('id', $packageId)
                ->where('status', CourseCreditPackage::STATUS_ACTIVE)
                ->first();

            if (! $package) {
                throw new BusinessException('Không tìm thấy gói lượt hợp lệ.', 404);
            }

            $now = now();

            $data = [
                'order_type' => Order::TYPE_INSTRUCTOR_CREDIT,
                'user_id' => $instructorId,
                'course_id' => null,
                'credit_package_id' => $package->id,
                'package_snapshot_name' => $package->name,
                'package_snapshot_credits' => $package->credits,
                'order_code' => $this->generateOrderCode(),
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'payment_method' => null,
                'provider_transaction_id' => null,
                'paid_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('orders', 'price_snapshot')) {
                $data['price_snapshot'] = $package->price;
            }

            if (Schema::hasColumn('orders', 'amount')) {
                $data['amount'] = $package->price;
            }

            if (Schema::hasColumn('orders', 'final_amount')) {
                $data['final_amount'] = $package->price;
            }

            if (Schema::hasColumn('orders', 'discount_amount')) {
                $data['discount_amount'] = 0;
            }

            if (Schema::hasColumn('orders', 'coupon_id')) {
                $data['coupon_id'] = null;
            }

            $orderId = DB::table('orders')->insertGetId($data);

            return Order::query()->findOrFail($orderId);
        });
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'CRD-' . now('Asia/Ho_Chi_Minh')->format('YmdHis') . random_int(1000, 9999);
        } while (DB::table('orders')->where('order_code', $code)->exists());

        return $code;
    }
}
