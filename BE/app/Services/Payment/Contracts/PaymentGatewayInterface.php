<?php

namespace App\Services\Payment\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create a payment url or qr code link for an order.
     */
    public function createPaymentUrl(object $order, float $amount): string;

    /**
     * Handle incoming webhook payload and verify it.
     * Returns an array with extracted details, or throws BusinessException on failure.
     */
    public function handleWebhook(array $payload): array;
}
