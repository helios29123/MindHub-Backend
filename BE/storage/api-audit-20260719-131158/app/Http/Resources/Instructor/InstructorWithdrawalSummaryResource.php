<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorWithdrawalSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payoutAccount = data_get($this->resource, 'payout_account');
        $data = [
            'available_revenue' => (float) data_get($this->resource, 'available_revenue', 0),
            'pending_withdraw_amount' => (float) data_get($this->resource, 'pending_withdraw_amount', 0),
            'paid_withdraw_amount' => (float) data_get($this->resource, 'paid_withdraw_amount', 0),
            'available_balance' => (float) data_get($this->resource, 'available_balance', 0),
            'can_create_withdrawal' => (bool) data_get($this->resource, 'can_create_withdrawal', false),
            'payout_account' => $payoutAccount ? (new InstructorPayoutAccountResource($payoutAccount))->resolve($request) : null,
        ];
        $notice = data_get($this->resource, 'notice');
        if ($notice !== null) {
            $data['notice'] = $notice;
        }
        return $data;
    }
}