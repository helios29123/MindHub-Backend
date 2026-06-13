<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\ApplyCouponRequest;
use App\Http\Requests\Payment\MyOrderQueryRequest;
use App\Http\Requests\Payment\PaymentWebhookRequest;
use App\Http\Requests\Payment\ShowOrderRequest;
use App\Http\Requests\Payment\StoreOrderRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\Payment\CouponApplyResource;
use App\Http\Resources\Payment\OrderResource;
use App\Http\Resources\Payment\PaymentResource;
use App\Services\Payment\CouponApplyService;
use App\Services\Payment\OrderService;
use App\Services\Payment\PaymentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

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
            $request->user()->id
        );

        return ApiResponse::success(
            new OrderResource($order),
            'Tạo đơn hàng thành công.',
            201
        );
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $order = $this->couponApplyService->applyCoupon(
            $request->validated(),
            $request->user()->id
        );

        return ApiResponse::success(
            new CouponApplyResource($order),
            'Áp mã giảm giá thành công.'
        );
    }

    public function storePayment(StorePaymentRequest $request): JsonResponse
    {
        $order = $this->paymentService->storePayment(
            $request->validated(),
            $request->user()->id
        );

        return ApiResponse::success(
            new PaymentResource($order),
            'Ghi nhận thanh toán thành công.'
        );
    }

    public function webhook(PaymentWebhookRequest $request): JsonResponse
    {
        $order = $this->paymentService->handleWebhook($request->validated());

        $message = $order->payment_status === 'paid' && $order->wasRecentlyCreated === false
            ? 'Đơn hàng đã được xử lý trước đó.'
            : 'Cập nhật trạng thái thanh toán thành công.';

        return ApiResponse::success(
            new PaymentResource($order),
            $message
        );
    }

    public function myOrders(MyOrderQueryRequest $request): JsonResponse
    {
        $orders = $this->orderService->getMyOrders(
            $request->validated(),
            $request->user()->id
        );

        return ApiResponse::paginated(
            OrderResource::collection($orders),
            $orders,
            'Lấy lịch sử đơn hàng và thanh toán thành công.'
        );
    }

    public function showOrder(ShowOrderRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        $order = $this->orderService->showUserOrder(
            (int) $validated['id'],
            $request->user()->id
        );

        return ApiResponse::success(
            new OrderResource($order),
            'Lấy trạng thái đơn hàng thành công.'
        );
    }
}