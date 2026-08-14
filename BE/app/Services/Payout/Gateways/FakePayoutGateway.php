<?php

namespace App\Services\Payout\Gateways;

use App\Exceptions\BusinessException;
use App\Models\WithdrawRequest;
use App\Services\Payout\Contracts\PayoutGatewayInterface;

class FakePayoutGateway implements PayoutGatewayInterface
{
    public function processPayout(WithdrawRequest $withdrawal): array
    {
        if (config('app.env') === 'production') {
            throw new BusinessException('FakePayoutGateway is not allowed in production.', 500);
        }

        $result = config('payout.fake.result', 'success');

        return [
            'status' => strtoupper($result),
            'message' => 'Fake payout result: ' . $result,
            'provider_payout_id' => 'FAKE-WD-' . $withdrawal->id,
            'payout_provider' => 'fake',
        ];
    }
}
