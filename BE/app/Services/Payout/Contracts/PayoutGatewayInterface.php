<?php

namespace App\Services\Payout\Contracts;

use App\Models\WithdrawRequest;

interface PayoutGatewayInterface
{
    /**
     * Process the payout for the given withdrawal request.
     *
     * @param WithdrawRequest $withdrawal
     * @return array Contains keys: 'status' (SUCCESS, FAILED, PROCESSING), 'message', 'provider_payout_id'
     */
    public function processPayout(WithdrawRequest $withdrawal): array;
}
