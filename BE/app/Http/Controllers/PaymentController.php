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

    public function createSePayPayment(StorePaymentRequest $request): JsonResponse
    {
        $result = $this->paymentService->createSePayPayment(
            $request->validated(),
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Tạo thông tin thanh toán SePay thành công.',
            'data' => $result,
        ]);
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
        $result = $this->paymentService->createSePayPayment(
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
