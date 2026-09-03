<?php

namespace App\Services\Payout\Gateways;

use App\Models\WithdrawRequest;
use App\Services\Payout\Contracts\PayoutGatewayInterface;

class ManualPayoutGateway implements PayoutGatewayInterface
{
    public function processPayout(WithdrawRequest $withdrawal): array
    {
        return [
            'status' => 'MANUAL_REQUIRED',
            'message' => 'Yêu cầu rút tiền cần xử lý thủ công qua chuyển khoản VietQR.',
            'provider_payout_id' => null,
            'payout_provider' => 'manual',
        ];
    }
}
