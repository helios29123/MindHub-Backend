<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MockSePayWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sepay:mock-webhook {orderId : ID của đơn hàng cần thanh toán}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi một webhook giả lập từ SePay đến server local để kiểm thử quá trình thanh toán';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = (int) $this->argument('orderId');
        $order = Order::find($orderId);

        if (!$order) {
            $this->error("Không tìm thấy đơn hàng ID: {$orderId}");
            return 1;
        }

        if ($order->payment_status === Order::PAYMENT_PAID) {
            $this->warn("Đơn hàng ID: {$orderId} đã được thanh toán rồi.");
        }

        // Calculate amount to pay
        $amount = (int) ($order->final_amount ?? $order->amount ?? $order->price_snapshot ?? $order->price ?? 0);

        if ($amount <= 0) {
            $this->error("Số tiền đơn hàng không hợp lệ (<= 0).");
            return 1;
        }

        $this->info("Đang tạo webhook giả lập cho đơn hàng MIND{$orderId} với số tiền " . number_format($amount) . " VND...");

        // Construct SePay Payload
        $payload = [
            'gateway' => 'sepay',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber' => config('sepay.bank_account', '123456789'),
            'subAccount' => null,
            'transferType' => 'in',
            'transferAmount' => $amount,
            'accumulated' => $amount,
            'code' => 'MOCK' . time(),
            'referenceCode' => 'REF' . time(),
            'description' => "MIND {$orderId}",
            'content' => "MIND {$orderId}",
        ];

        $rawBody = json_encode($payload);
        $secret = config('sepay.webhook_secret');

        if (empty($secret)) {
            $this->error("Chưa cấu hình SEPAY_WEBHOOK_SECRET trong file .env");
            return 1;
        }

        // Generate correct HMAC-SHA256 signature
        $signature = hash_hmac('sha256', $rawBody, $secret);

        // Determine URL (local app url)
        $url = url('/api/payments/sepay/webhook');
        
        $this->info("Gửi POST request đến: {$url}");
        $this->info("Chữ ký X-SePay-Signature: {$signature}");

        // Send Request
        $response = Http::withHeaders([
            'X-SePay-Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($url, $payload);

        if ($response->successful()) {
            $this->info("Thành công! Server trả về: " . $response->body());
            return 0;
        } else {
            $this->error("Thất bại! HTTP Status: " . $response->status());
            $this->error("Lỗi từ server: " . $response->body());
            return 1;
        }
    }
}
