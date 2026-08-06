<?php

use App\Exceptions\BusinessException;
use App\Services\Payment\Gateways\SePayGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->gateway = new SePayGateway();
    $this->secret = 'whsec_test_secret_123';
    Config::set('sepay.webhook_secret', $this->secret);

    $this->payload = [
        'gateway' => 'sepay',
        'transactionDate' => '2023-10-10 10:10:10',
        'transferAmount' => 100000,
        'content' => 'MIND 123',
        'referenceCode' => 'REF123456',
    ];
});

it('rejects webhook if webhook_secret is missing', function () {
    Config::set('sepay.webhook_secret', '');

    $this->expectException(BusinessException::class);
    $this->expectExceptionMessage('Cấu hình SePay Webhook chưa đầy đủ.');
    $this->expectExceptionCode(500);

    $this->gateway->handleWebhook($this->payload);
});

it('rejects webhook if signature header is missing', function () {
    $request = Request::create('/api/payments/sepay/webhook', 'POST', [], [], [], [], json_encode($this->payload));
    app()->instance('request', $request);

    $this->expectException(BusinessException::class);
    $this->expectExceptionMessage('Thiếu chữ ký xác thực SePay Webhook.');
    $this->expectExceptionCode(401);

    $this->gateway->handleWebhook($this->payload);
});

it('rejects webhook if signature is invalid', function () {
    $rawBody = json_encode($this->payload);
    
    $request = Request::create('/api/payments/sepay/webhook', 'POST', [], [], [], [
        'HTTP_X_SEPAY_SIGNATURE' => 'invalid_signature',
    ], $rawBody);
    app()->instance('request', $request);

    $this->expectException(BusinessException::class);
    $this->expectExceptionMessage('Xác thực SePay Webhook thất bại. Chữ ký không hợp lệ.');
    $this->expectExceptionCode(401);

    $this->gateway->handleWebhook($this->payload);
});

it('accepts webhook if signature is valid', function () {
    $rawBody = json_encode($this->payload);
    $validSignature = hash_hmac('sha256', $rawBody, $this->secret);
    
    $request = Request::create('/api/payments/sepay/webhook', 'POST', [], [], [], [
        'HTTP_X_SEPAY_SIGNATURE' => $validSignature,
    ], $rawBody);
    app()->instance('request', $request);

    $result = $this->gateway->handleWebhook($this->payload);

    expect($result['order_id'])->toBe(123)
        ->and($result['amount'])->toBe(100000.0)
        ->and($result['provider_transaction_id'])->toBe('REF123456')
        ->and($result['payment_method'])->toBe('sepay');
});
