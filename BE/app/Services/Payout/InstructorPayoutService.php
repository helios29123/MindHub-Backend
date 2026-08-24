<?php

namespace App\Services\Payout;

use App\Exceptions\MinimumPayoutNotReachedException;
use App\Exceptions\PayoutAccountMissingException;
use App\Exceptions\PayoutAccountUnverifiedException;
use App\Exceptions\PayoutPeriodAlreadyExistsException;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\User;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class InstructorPayoutService
{
    /**
     * Generate periodic monthly payout for a single instructor.
     */
    public function generateMonthlyPayout(int $instructorId, ?CarbonInterface $periodEnd = null): ?WithdrawRequest
    {
        $periodEnd = $periodEnd ? Carbon::parse($periodEnd) : now()->endOfMonth();
        $periodStart = (clone $periodEnd)->startOfMonth();

        return DB::transaction(function () use ($instructorId, $periodStart, $periodEnd) {
            // Check if payout statement already exists for this instructor and period
            $existingPayout = WithdrawRequest::query()
                ->where('user_id', $instructorId)
                ->where('period_start', $periodStart->toDateString())
                ->where('period_end', $periodEnd->toDateString())
                ->lockForUpdate()
                ->first();

            if ($existingPayout) {
                return $existingPayout;
            }

            // Lock eligible available revenues
            $revenues = Revenue::query()
                ->where('instructor_id', $instructorId)
                ->where('status', Revenue::STATUS_AVAILABLE)
                ->whereNull('payout_id')
                ->where('available_at', '<=', $periodEnd)
                ->lockForUpdate()
                ->get();

            $totalAmount = round((float) $revenues->sum('instructor_amount'), 2);
            $minimumAmount = (float) config('revenue.payout.minimum_amount', 200000);

            if ($totalAmount <= 0) {
                return null;
            }

            // Check payout account
            $account = PayoutAccount::query()
                ->where('user_id', $instructorId)
                ->where(function ($q) {
                    $q->where('is_default', true)
                      ->orWhere('status', PayoutAccount::STATUS_ACTIVE);
                })
                ->latest()
                ->first();

            $status = WithdrawRequest::STATUS_READY_TO_PAY;
            $blockedReason = null;

            if (! $account) {
                $status = WithdrawRequest::STATUS_BLOCKED;
                $blockedReason = 'missing_payout_account';
            } elseif ($account->status !== PayoutAccount::STATUS_ACTIVE && empty($account->approved_at)) {
                $status = WithdrawRequest::STATUS_BLOCKED;
                $blockedReason = 'unverified_payout_account';
            } elseif ($totalAmount < $minimumAmount) {
                $status = WithdrawRequest::STATUS_BLOCKED;
                $blockedReason = 'minimum_payout_not_reached';
            }

            // Expected payment date (e.g. 5th-10th of next month)
            $windowStartDay = (int) config('revenue.payout.window_start_day', 5);
            $expectedPaymentAt = (clone $periodEnd)->addMonth()->day($windowStartDay)->startOfDay();

            $payout = WithdrawRequest::query()->create([
                'user_id' => $instructorId,
                'payout_account_id' => $account?->id,
                'amount' => $totalAmount,
                'status' => $status,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'expected_payment_at' => $expectedPaymentAt,
                'requested_at' => now(),
                'bank_name' => $account?->provider ?? $account?->bank_name ?? 'Bank Transfer',
                'account_number_snapshot' => $account?->account_number ? substr($account->account_number, -4) : null,
                'account_name_snapshot' => $account?->account_name ?? $account?->account_holder_name,
                'payout_method' => 'bank_transfer',
                'blocked_reason' => $blockedReason,
            ]);

            // Link revenues to payout
            foreach ($revenues as $revenue) {
                $revenue->update([
                    'payout_id' => $payout->id,
                    'status' => $status === WithdrawRequest::STATUS_READY_TO_PAY
                        ? Revenue::STATUS_INCLUDED_IN_PAYOUT
                        : Revenue::STATUS_SCHEDULED,
                    'updated_at' => now(),
                ]);
            }

            return $payout;
        });
    }

    /**
     * Generate monthly payouts for all instructors with available revenue.
     */
    public function generateAllMonthlyPayouts(?CarbonInterface $periodEnd = null): array
    {
        $periodEnd = $periodEnd ? Carbon::parse($periodEnd) : now()->endOfMonth();

        $instructorIds = Revenue::query()
            ->where('status', Revenue::STATUS_AVAILABLE)
            ->whereNull('payout_id')
            ->where('available_at', '<=', $periodEnd)
            ->distinct()
            ->pluck('instructor_id');

        $payouts = [];
        foreach ($instructorIds as $instructorId) {
            $payout = $this->generateMonthlyPayout((int) $instructorId, $periodEnd);
            if ($payout) {
                $payouts[] = $payout;
            }
        }

        return $payouts;
    }

    /**
     * Process ready payouts (simulating manual or automated batch processing).
     */
    public function processReadyPayouts(): int
    {
        return DB::transaction(function () {
            $readyPayouts = WithdrawRequest::query()
                ->whereIn('status', [WithdrawRequest::STATUS_READY_TO_PAY, WithdrawRequest::STATUS_QUEUED])
                ->lockForUpdate()
                ->get();

            $processedCount = 0;
            foreach ($readyPayouts as $payout) {
                $payout->update([
                    'status' => WithdrawRequest::STATUS_PAID,
                    'paid_at' => now(),
                    'processed_at' => now(),
                ]);

                Revenue::query()
                    ->where('payout_id', $payout->id)
                    ->update([
                        'status' => Revenue::STATUS_PAID,
                        'updated_at' => now(),
                    ]);

                // Send Email & Notification to Instructor for monthly batch payout
                try {
                    $user = $payout->user;
                    if ($user && $user->email) {
                        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WithdrawalSuccessInstructorMail($payout, $user));

                        \App\Models\Notification::create([
                            'user_id' => $user->id,
                            'type' => 'payout_success',
                            'title' => 'Chuyển tiền định kỳ thành công',
                            'message' => "Yêu cầu thanh toán #" . $payout->id . " số tiền " . number_format($payout->amount, 0, ',', '.') . " đ đã được chuyển thành công vào tài khoản ngân hàng của bạn.",
                            'data' => json_encode(['withdrawal_id' => $payout->id, 'amount' => $payout->amount]),
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to send monthly payout success email: ' . $e->getMessage());
                }

                $processedCount++;
            }

            return $processedCount;
        });
    }

    /**
     * Audit and reconcile payout statements against revenue line items.
     */
    public function reconcilePayouts(?int $instructorId = null): array
    {
        $query = WithdrawRequest::query();
        if ($instructorId) {
            $query->where('user_id', $instructorId);
        }

        $payouts = $query->with('revenues')->get();
        $discrepancies = [];

        foreach ($payouts as $payout) {
            $sumRevenues = round((float) $payout->revenues->sum('instructor_amount'), 2);
            $payoutAmount = round((float) $payout->amount, 2);

            if ($sumRevenues !== $payoutAmount) {
                $discrepancies[] = [
                    'payout_id' => $payout->id,
                    'instructor_id' => $payout->user_id,
                    'payout_amount' => $payoutAmount,
                    'revenue_sum' => $sumRevenues,
                    'difference' => round($payoutAmount - $sumRevenues, 2),
                ];
            }
        }

        return [
            'total_checked' => $payouts->count(),
            'discrepancies_found' => count($discrepancies),
            'details' => $discrepancies,
        ];
    }
}
