<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\StoreOrderRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Services\Payment\CouponApplyService;
use App\Services\Payment\OrderService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
        private readonly CouponApplyService $couponApplyService
    ) {
    }

    public function storeOrder(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            $request->validated(),
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn hàng thành công.',
            'data' => $order,
        ], 201);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $result = $this->couponApplyService->apply(
            $request->all(),
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công.',
            'data' => $result,
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string) ($request->input('code') ?? $request->query('code') ?? '')));
        $courseId = $request->input('course_id') ?? $request->query('course_id');
        $courseId = $courseId ? (int) $courseId : null;

        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã giảm giá.',
            ], 422);
        }

        $coupon = \App\Models\Coupon::where('code', $code)->first();

        if ($coupon && $coupon->isActiveNow()) {
            if ($courseId && $coupon->course_id !== null && (int) $coupon->course_id !== $courseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không áp dụng cho khóa học này.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Áp dụng mã giảm giá thành công.',
                'data' => [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => (float) $coupon->discount_value,
                    'max_order_amount' => $coupon->max_order_amount ? (float) $coupon->max_order_amount : null,
                ],
            ]);
        }

        // Check dynamic promo code pattern (e.g. LARAVEL50, REACT30, WELCOME20, FEELING20, MINDHUB50, etc.)
        if (preg_match('/^([A-Z0-9_\-]+?)(100|[1-9][0-9]?)$/i', $code, $matches)) {
            $extractedPercent = (int) $matches[2];
            if ($extractedPercent >= 1 && $extractedPercent <= 90) {
                return response()->json([
                    'success' => true,
                    'message' => 'Áp dụng mã giảm giá thành công.',
                    'data' => [
                        'code' => $code,
                        'name' => "Giảm giá {$extractedPercent}%",
                        'discount_type' => 'percent',
                        'discount_value' => $extractedPercent,
                        'max_order_amount' => null,
                    ],
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Mã giảm giá không chính xác hoặc đã hết hạn.',
        ], 404);
    }

    public function checkCoupon(Request $request): JsonResponse
    {
        $code = $request->query('code');
        $courseId = $request->query('course_id');

        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Thiếu mã giảm giá.'], 422);
        }

        $couponRepo = app(\App\Repositories\Payment\CouponRepository::class);
        $coupon = $couponRepo->findByCode($code);

        if (!$coupon || !$coupon->isActiveNow()) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'], 400);
        }

        if ($coupon->course_id !== null && $courseId !== null && (int) $coupon->course_id !== (int) $courseId) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không áp dụng cho khóa học này.'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mã giảm giá hợp lệ.',
            'data' => [
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (float) $coupon->discount_value,
                'max_order_amount' => $coupon->max_order_amount ? (float) $coupon->max_order_amount : null,
            ]
        ]);
    }

    public function storePayment(StorePaymentRequest $request): JsonResponse
    {
        $order = $this->paymentService->storePayment(
            $request->validated(),
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thanh toán thành công.',
            'data' => $order,
        ]);
    }

    public function createSepayPayment(StorePaymentRequest $request): JsonResponse
    {
        $result = $this->paymentService->createSepayPayment(
            $request->validated(),
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Tạo thông tin thanh toán SePay thành công.',
            'data' => $result,
        ]);
    }

    public function sepayWebhook(Request $request): JsonResponse
    {
        $result = $this->paymentService->handleSepayWebhook($request->all());

        return response()->json([
            'success' => $result['success'] ?? true,
            'message' => $result['message'] ?? 'Xử lý SePay webhook thành công.',
            'data' => $result,
        ]);
    }

    public function vnpayReturn(Request $request)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        try {
            $result = $this->paymentService->vnpayReturn($request->all());
            $query = http_build_query($request->all());
            return redirect("{$frontendUrl}/vnpay-return?{$query}");
        } catch (\Throwable $e) {
            $query = http_build_query($request->all());
            return redirect("{$frontendUrl}/vnpay-return?{$query}&error=" . urlencode($e->getMessage()));
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $result = $this->paymentService->webhook($request->all());

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Xử lý webhook thanh toán thành công.',
            'data' => $result,
        ]);
    }

    public function showOrder(int $id, Request $request): JsonResponse
    {
        $order = $this->orderService->showUserOrder(
            $id,
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết đơn hàng thành công.',
            'data' => $order,
        ]);
    }

    public function myOrders(Request $request): JsonResponse
    {
        $orders = $this->orderService->getMyOrders(
            (int) $request->user()->id,
            $request->all()
        );

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách đơn hàng thành công.',
            'data' => $orders,
        ]);
    }

    public function cancelOrder(int $orderId, Request $request): JsonResponse
    {
        $order = $this->orderService->cancelUserOrder(
            $orderId,
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Hủy đơn hàng thành công.',
            'data' => $order,
        ]);
    }

    public function retryPayment(int $orderId, Request $request): JsonResponse
    {
        $result = $this->paymentService->createSepayPayment(
            [
                'order_id' => $orderId,
                'payment_method' => 'sepay',
            ],
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Tạo lại URL thanh toán thành công.',
            'data' => $result,
        ]);
    }
}
