<?php

namespace App\Http\Controllers;


// use App\Http\Requests\Payment\MyOrderQueryRequest;
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
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Payment\ApplyCouponRequest;

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

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn hàng thành công.',
            'data' => new OrderResource($order),
        ], 201);
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $order = $this->couponApplyService->applyCoupon(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Áp mã giảm giá thành công.',
            'data' => new CouponApplyResource($order),
        ]);
    }

    public function storePayment(StorePaymentRequest $request): JsonResponse
    {
        $order = $this->paymentService->storePayment(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Ghi nhận thanh toán thành công.',
            'data' => new PaymentResource($order),
        ]);
    }

    public function webhook(PaymentWebhookRequest $request): JsonResponse
{
    $order = $this->paymentService->handleWebhook($request->validated());

    $message = $order->payment_status === 'paid'
        && $order->wasRecentlyCreated === false
        ? 'Đơn hàng đã được xử lý trước đó.'
        : 'Cập nhật trạng thái thanh toán thành công.';

    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => new PaymentResource($order),
    ]);
}

    public function showOrder(ShowOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->showUserOrder(
            $id,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Lấy trạng thái đơn hàng thành công.',
            'data' => new OrderResource($order),
        ]);
    }

    // public function myOrders(MyOrderQueryRequest $request): JsonResponse
    // {
    //     $orders = $this->orderService->getMyOrders(
    //         $request->validated(),
    //         $request->user()->id
    //     );

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Lấy lịch sử đơn hàng và thanh toán thành công.',
    //         'data' => OrderResource::collection($orders->items()),
    //         'meta' => [
    //             'current_page' => $orders->currentPage(),
    //             'last_page' => $orders->lastPage(),
    //             'per_page' => $orders->perPage(),
    //             'total' => $orders->total(),
    //         ],
    //     ]);
    // }
}
