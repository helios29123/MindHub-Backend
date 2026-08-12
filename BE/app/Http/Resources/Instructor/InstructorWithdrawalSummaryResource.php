<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InstructorWithdrawalSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payoutAccount = data_get($this->resource, 'payout_account');

        return [
            'page_title' => data_get($this->resource, 'page_title', 'Thanh toán giảng viên'),
            'pending_revenue' => (float) data_get($this->resource, 'pending_revenue', 0),
            'available_balance' => (float) data_get($this->resource, 'available_balance', 0),
            'reserved_balance' => (float) data_get($this->resource, 'reserved_balance', 0),
            'scheduled_payout' => (float) data_get($this->resource, 'scheduled_payout', 0),
            'early_withdrawable_balance' => (float) data_get($this->resource, 'early_withdrawable_balance', 0),
            'total_paid' => (float) data_get($this->resource, 'total_paid', 0),
            'blocked_amount' => (float) data_get($this->resource, 'blocked_amount', 0),
            'minimum_payout' => (float) data_get($this->resource, 'minimum_payout', 200000),
            'minimum_early_withdrawal' => (float) data_get($this->resource, 'minimum_early_withdrawal', 200000),
            'has_active_early_withdrawal' => (bool) data_get($this->resource, 'has_active_early_withdrawal', false),
            'early_withdrawal_requests_remaining' => (int) data_get($this->resource, 'early_withdrawal_requests_remaining', 2),
            'next_early_withdrawal_available_at' => data_get($this->resource, 'next_early_withdrawal_available_at'),
            'automatic_payout_window' => data_get($this->resource, 'automatic_payout_window', []),
            'payout_account_verified' => (bool) data_get($this->resource, 'payout_account_verified', false),
            'blocked_reason' => data_get($this->resource, 'blocked_reason'),
            'payout_account' => $payoutAccount ? (new InstructorPayoutAccountResource($payoutAccount))->resolve($request) : null,
            // Legacy / UI compatibility fields
            'available_revenue' => (float) data_get($this->resource, 'available_revenue', 0),
            'pending_withdraw_amount' => (float) data_get($this->resource, 'pending_withdraw_amount', 0),
            'paid_withdraw_amount' => (float) data_get($this->resource, 'total_paid', 0),
            'paid_amount' => (float) data_get($this->resource, 'total_paid', 0),
            'can_create_withdrawal' => (bool) data_get($this->resource, 'can_create_withdrawal', false),
            'notice' => data_get($this->resource, 'notice'),
        ];
    }
}