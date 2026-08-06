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
        // 1. Authenticate (optional based on SePay webhook config)
        $secret = config('sepay.webhook_secret');
        if (!empty($secret)) {
            $authHeader = request()->header('Authorization');
            if (empty($authHeader) || $authHeader !== 'Bearer ' . $secret) {
                // For better flexibility if it's sent differently
                $authHeader2 = request()->header('Apikey');
                if ($authHeader2 !== $secret) {
                    throw new BusinessException('Xác thực SePay Webhook thất bại.', 401);
                }
            }
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
