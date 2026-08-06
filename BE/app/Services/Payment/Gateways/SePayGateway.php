<?php

namespace App\Services\Payment\Gateways;

use App\Exceptions\BusinessException;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class SePayGateway implements PaymentGatewayInterface
{
    public function createPaymentUrl(object $order, float $amount): string
    {
        $baseUrl = config('sepay.api_base_url', 'https://qr.sepay.vn');
        $bankAccount = config('sepay.bank_account');
        $bankCode = config('sepay.bank_code');
        $accountName = config('sepay.account_name');
        
        if (empty($bankAccount) || empty($bankCode)) {
            throw new BusinessException('Chưa cấu hình SePay Bank Account và Bank Code.', 500);
        }

        $memo = 'MIND' . $order->id; // Using MIND + order ID
        
        $url = $baseUrl . '/img' 
            . '?acc=' . urlencode($bankAccount)
            . '&bank=' . urlencode($bankCode)
            . '&amount=' . urlencode((string) (int) round($amount))
            . '&des=' . urlencode($memo);
            
        if (!empty($accountName)) {
            $url .= '&name=' . urlencode($accountName);
        }

        return $url;
    }

    public function handleWebhook(array $payload): array
    {
        // 1. Authenticate SePay Webhook using HMAC-SHA256
        $secret = config('sepay.webhook_secret');
        
        if (empty($secret)) {
            Log::error('SePay Webhook Verification Failed: Missing webhook_secret configuration.');
            throw new BusinessException('Cấu hình SePay Webhook chưa đầy đủ.', 500);
        }

        $signatureHeader = (string) request()->header('X-SePay-Signature');
        
        if (empty($signatureHeader)) {
            Log::warning('SePay Webhook Verification Failed: Missing X-SePay-Signature header.');
            throw new BusinessException('Thiếu chữ ký xác thực SePay Webhook.', 401);
        }

        $rawRequestBody = request()->getContent();
        
        $expectedSignature = hash_hmac('sha256', $rawRequestBody, $secret);

        if (!hash_equals($expectedSignature, $signatureHeader)) {
            Log::warning('SePay Webhook Verification Failed: Signature mismatch.');
            throw new BusinessException('Xác thực SePay Webhook thất bại. Chữ ký không hợp lệ.', 401);
        }

        // 2. Validate payload
        $gateway = $payload['gateway'] ?? '';
        $transactionDate = $payload['transactionDate'] ?? '';
        $transferAmount = $payload['transferAmount'] ?? 0;
        $content = $payload['content'] ?? '';
        $referenceCode = $payload['referenceCode'] ?? '';

        if (empty($content) || empty($referenceCode)) {
            throw new BusinessException('Thiếu thông tin content hoặc referenceCode.', 422);
        }

        // Extract Order ID from content (assuming format MIND{id} or MIND {id})
        // Remove spaces and normalize
        $normalizedContent = strtoupper(preg_replace('/\s+/', '', $content));
        
        if (!preg_match('/MIND(\d+)/', $normalizedContent, $matches)) {
            throw new BusinessException('Nội dung chuyển khoản không hợp lệ.', 422);
        }

        $orderId = (int) $matches[1];

        return [
            'order_id' => $orderId,
            'amount' => (float) $transferAmount,
            'provider_transaction_id' => $referenceCode,
            'payment_method' => 'sepay',
            'raw' => $payload,
        ];
    }
}
