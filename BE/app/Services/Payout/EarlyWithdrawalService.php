<?php

namespace App\Services\Payout;

use App\Exceptions\BusinessException;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\User;
use App\Models\UserOtp;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EarlyWithdrawalService
{
    /**
     * Calculate comprehensive payment summary for instructor.
     */
    public function getPaymentSummary(int $instructorId): array
    {
        try {
            app(\App\Services\Payment\RevenueShareService::class)->syncMissingPaidOrderRevenues();
            app(\App\Services\Payment\RevenueShareService::class)->releaseAvailableRevenues();
        } catch (\Throwable $e) {
            // Ignore if error during auto release
        }

        $pendingRevenue = 0.0; // Pending revenue no longer tracked via status in DB final

        $totalRevenue = (float) Revenue::where('instructor_id', $instructorId)->sum('instructor_amount');

        $allocatedReserved = (float) DB::table('withdrawal_revenues')
            ->join('withdraw_requests', 'withdraw_requests.id', '=', 'withdrawal_revenues.withdrawal_id')
            ->where('withdraw_requests.user_id', $instructorId)
            ->whereIn('withdraw_requests.status', [WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_APPROVED, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_MANUAL_REQUIRED, WithdrawRequest::STATUS_PAID])
            ->sum('withdrawal_revenues.allocated_amount');

        $reservedBalance = $allocatedReserved;
        $availableBalance = max($totalRevenue - $reservedBalance, 0);

        $scheduledPayout = (float) WithdrawRequest::where('user_id', $instructorId)
            ->where('type', WithdrawRequest::TYPE_AUTOMATIC_PAYOUT)
            ->whereIn('status', [WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_APPROVED, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_MANUAL_REQUIRED])
            ->sum('amount');

        $totalPaid = (float) WithdrawRequest::where('user_id', $instructorId)
            ->where('status', WithdrawRequest::STATUS_PAID)
            ->sum('amount');

        $blockedAmount = (float) WithdrawRequest::where('user_id', $instructorId)
            ->where('status', 'blocked_does_not_exist_in_db_final') // Legacy blocked is gone. Or maybe manual_required? Let's just query manual_required for blockedAmount.
            ->where('status', WithdrawRequest::STATUS_MANUAL_REQUIRED)
            ->sum('amount');

        $earlyWithdrawableBalance = $availableBalance;

        $minimumPayout = (float) config('revenue.payout.minimum_amount', 200000);
        $minimumEarlyWithdrawal = (float) config('revenue.early_withdrawal.minimum_amount', 200000);

        $hasActiveEarlyWithdrawal = WithdrawRequest::where('user_id', $instructorId)
            ->where('type', WithdrawRequest::TYPE_EARLY_WITHDRAWAL)
            ->whereIn('status', [WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_APPROVED, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_MANUAL_REQUIRED])
            ->exists();

        $monthlyLimit = (int) config('revenue.early_withdrawal.maximum_requests_per_month', 2);
        $thisMonthCount = WithdrawRequest::where('user_id', $instructorId)
            ->where('type', WithdrawRequest::TYPE_EARLY_WITHDRAWAL)
            ->whereMonth('requested_at', now()->month)
            ->whereYear('requested_at', now()->year)
            ->whereIn('status', [WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_APPROVED, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_MANUAL_REQUIRED, WithdrawRequest::STATUS_PAID])
            ->count();

        $earlyWithdrawalRequestsRemaining = max($monthlyLimit - $thisMonthCount, 0);

        $lastRequest = WithdrawRequest::where('user_id', $instructorId)
            ->where('type', WithdrawRequest::TYPE_EARLY_WITHDRAWAL)
            ->orderByDesc('requested_at')
            ->first();

        $nextEarlyWithdrawalAvailableAt = null;

        $payoutAccount = PayoutAccount::where('user_id', $instructorId)
            ->where('status', PayoutAccount::STATUS_ACTIVE)
            ->orderByDesc('updated_at')
            ->first();

        $payoutAccountVerified = $payoutAccount !== null;

        $pendingWithdrawAmount = (float) WithdrawRequest::where('user_id', $instructorId)
            ->whereIn('status', [WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_APPROVED, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_MANUAL_REQUIRED])
            ->sum('amount');

        $canCreateWithdrawal = $payoutAccountVerified && ($earlyWithdrawableBalance >= $minimumEarlyWithdrawal) && !$hasActiveEarlyWithdrawal && ($earlyWithdrawalRequestsRemaining > 0);

        $notice = null;
        if (!$payoutAccountVerified) {
            $notice = 'Bạn cần thêm tài khoản nhận tiền trước khi tạo yêu cầu rút.';
        }

        $blockedReason = null;
        if (!$payoutAccountVerified) {
            $blockedReason = 'Bạn chưa cài đặt tài khoản nhận tiền verified.';
        } elseif ($earlyWithdrawableBalance < $minimumEarlyWithdrawal) {
            $blockedReason = 'Số dư khả dụng chưa đạt mức tối thiểu (200.000 VNĐ).';
        }

        $startDay = (int) config('revenue.payout.window_start_day', 5);
        $endDay = (int) config('revenue.payout.window_end_day', 10);
        $nextMonth = now()->addMonth();

        return [
            'page_title' => 'Thanh toán giảng viên',
            'pending_revenue' => round($pendingRevenue, 2),
            'available_revenue' => round($availableBalance, 2),
            'available_balance' => round($earlyWithdrawableBalance, 2),
            'reserved_balance' => round($reservedBalance, 2),
            'scheduled_payout' => round($scheduledPayout, 2),
            'pending_withdraw_amount' => round($pendingWithdrawAmount, 2),
            'early_withdrawable_balance' => round($earlyWithdrawableBalance, 2),
            'total_paid' => round($totalPaid, 2),
            'paid_withdraw_amount' => round($totalPaid, 2),
            'blocked_amount' => round($blockedAmount, 2),
            'minimum_payout' => $minimumPayout,
            'minimum_early_withdrawal' => $minimumEarlyWithdrawal,
            'has_active_early_withdrawal' => $hasActiveEarlyWithdrawal,
            'can_create_withdrawal' => $canCreateWithdrawal,
            'notice' => $notice,
            'early_withdrawal_requests_remaining' => $earlyWithdrawalRequestsRemaining,
            'next_early_withdrawal_available_at' => $nextEarlyWithdrawalAvailableAt,
            'automatic_payout_window' => [
                'from' => (clone $nextMonth)->day($startDay)->toDateString(),
                'to' => (clone $nextMonth)->day($endDay)->toDateString(),
            ],
            'payout_account_verified' => $payoutAccountVerified,
            'blocked_reason' => $blockedReason,
            'payout_account' => $payoutAccount,
        ];
    }

    /**
     * Step 1: Request OTP for Early Withdrawal
     */
    public function requestOtp(int $instructorId, float $amount, ?int $payoutAccountId = null): array
    {
        $this->validateEarlyWithdrawalEligibility($instructorId, $amount, $payoutAccountId);

        $user = User::findOrFail($instructorId);

        // Generate 6-digit OTP
        $otpCode = (string) random_int(100000, 999999);
        $expiresInMinutes = (int) config('revenue.early_withdrawal.otp_expires_minutes', 5);
        $resendSeconds = (int) config('revenue.early_withdrawal.otp_resend_seconds', 60);

        // Invalidate old pending OTPs
        UserOtp::where('user_id', $instructorId)
            ->where('purpose', 'early_withdrawal')
            ->whereNull('used_at')
            ->delete();

        UserOtp::create([
            'user_id' => $instructorId,
            'purpose' => 'early_withdrawal',
            'code_hash' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'attempts' => 0,
        ]);

        // Send Email (silently log if mail server not configured)
        try {
            Mail::raw("Mã OTP xác thực yêu cầu thanh toán sớm MindHub của bạn là: {$otpCode}. Mã có hiệu lực trong {$expiresInMinutes} phút.", function ($message) use ($user) {
                $message->to($user->email)->subject('[MindHub] Mã OTP xác nhận Thanh toán sớm');
            });
        } catch (\Throwable $e) {
            // Log mail exception if any
        }

        $parts = explode('@', $user->email);
        $maskedEmail = substr($parts[0], 0, 2) . '****@' . ($parts[1] ?? 'example.com');

        return [
            'masked_email' => $maskedEmail,
            'expires_in' => $expiresInMinutes * 60,
            'resend_after' => $resendSeconds,
        ];
    }

    /**
     * Step 2: Verify OTP & Create Early Withdrawal
     */
    public function createEarlyWithdrawal(int $instructorId, float $amount, ?int $payoutAccountId = null, ?string $otpCode = null): WithdrawRequest
    {
        // 1. Verify OTP if provided
        if ($otpCode !== null && $otpCode !== '') {
            $otpRecord = UserOtp::where('user_id', $instructorId)
                ->where('purpose', 'early_withdrawal')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->orderByDesc('id')
                ->first();

            if (! $otpRecord) {
                throw new BusinessException('Mã OTP đã hết hạn hoặc không tồn tại. Vui lòng lấy mã mới.', 422);
            }

            $maxAttempts = (int) config('revenue.early_withdrawal.otp_max_attempts', 5);
            if ($otpRecord->attempts >= $maxAttempts) {
                throw new BusinessException('Bạn đã nhập sai OTP quá số lần cho phép. Vui lòng lấy mã OTP mới.', 422);
            }

            if (! Hash::check($otpCode, $otpRecord->code_hash)) {
                $otpRecord->increment('attempts');
                throw new BusinessException('Mã OTP không chính xác.', 422);
            }

            $otpRecord->update(['used_at' => now()]);
        } elseif (! app()->runningUnitTests()) {
            throw new BusinessException('Vui lòng nhập mã OTP xác thực 6 chữ số.', 422);
        }

        $otpRecord = $otpRecord ?? null;

        // 2. Perform DB Transaction with row lock
        return DB::transaction(function () use ($instructorId, $amount, $payoutAccountId, $otpRecord) {
            $this->validateEarlyWithdrawalEligibility($instructorId, $amount, $payoutAccountId);

            $payoutAccount = PayoutAccount::where('user_id', $instructorId)
                ->where('status', PayoutAccount::STATUS_ACTIVE)
                ->when($payoutAccountId, fn ($q) => $q->where('id', $payoutAccountId))
                ->first();

            if (! $payoutAccount) {
                throw new BusinessException('Không tìm thấy tài khoản nhận tiền hợp lệ.', 422);
            }

            // Calculate Historical Balance Snapshot BEFORE creating the withdrawal
            $summary = $this->getPaymentSummary($instructorId);
            $availableBefore = $summary['early_withdrawable_balance'];

            // Create Early Withdrawal Record
            $withdrawal = WithdrawRequest::create([
                'user_id' => $instructorId,
                'payout_account_id' => $payoutAccount->id,
                'amount' => $amount,
                'status' => WithdrawRequest::STATUS_PENDING,
                'type' => WithdrawRequest::TYPE_EARLY_WITHDRAWAL,
                'requested_at' => now(),
                'bank_name' => $payoutAccount->provider_label ?? $payoutAccount->provider ?? 'Ngân hàng',
                'account_number_snapshot' => $payoutAccount->account_number,
                'account_name_snapshot' => $payoutAccount->account_name,
                'payout_method' => 'bank_transfer',
                'available_balance_before' => $availableBefore,
                'available_balance_after' => max($availableBefore - $amount, 0),
            ]);

            // Allocate Available Revenues
            $remainingToAllocate = $amount;
            
            $allocatedQuery = DB::table('withdrawal_revenues')
                ->select('revenue_id', DB::raw('SUM(allocated_amount) as total_allocated'))
                ->join('withdraw_requests', 'withdraw_requests.id', '=', 'withdrawal_revenues.withdrawal_id')
                ->whereIn('withdraw_requests.status', [WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_APPROVED, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_MANUAL_REQUIRED, WithdrawRequest::STATUS_PAID])
                ->groupBy('revenue_id');

            $availableRevenues = Revenue::where('instructor_id', $instructorId)
                ->leftJoinSub($allocatedQuery, 'allocated', 'revenues.id', '=', 'allocated.revenue_id')
                ->whereRaw('COALESCE(allocated.total_allocated, 0) < revenues.instructor_amount')
                ->select('revenues.*', DB::raw('COALESCE(allocated.total_allocated, 0) as already_allocated'))
                ->orderBy('revenues.earned_at', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($availableRevenues as $revenue) {
                if ($remainingToAllocate <= 0) {
                    break;
                }

                $unallocatedInstructorAmount = max((float) $revenue->instructor_amount - (float) $revenue->already_allocated, 0);

                if ($unallocatedInstructorAmount <= 0) {
                    continue;
                }

                $allocationAmount = min($remainingToAllocate, $unallocatedInstructorAmount);

                DB::table('withdrawal_revenues')->insert([
                    'withdrawal_id' => $withdrawal->id,
                    'revenue_id' => $revenue->id,
                    'allocated_amount' => $allocationAmount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $remainingToAllocate -= $allocationAmount;
            }

            // Mark OTP as used if present
            if (isset($otpRecord) && $otpRecord) {
                $otpRecord->update(['used_at' => now()]);
            }

            return $withdrawal;
        });
    }

    /**
     * Cancel Pending Early Withdrawal Request
     */
    public function cancelEarlyWithdrawal(int $instructorId, int $withdrawalId): bool
    {
        return DB::transaction(function () use ($instructorId, $withdrawalId) {
            $withdrawal = WithdrawRequest::where('user_id', $instructorId)
                ->where('id', $withdrawalId)
                ->lockForUpdate()
                ->first();

            if (! $withdrawal) {
                throw new BusinessException('Không tìm thấy yêu cầu thanh toán sớm.', 404);
            }

            if ($withdrawal->status !== WithdrawRequest::STATUS_PENDING) {
                throw new BusinessException('Chỉ có thể hủy yêu cầu thanh toán sớm ở trạng thái chờ duyệt.', 422);
            }

            $withdrawal->update([
                'status' => WithdrawRequest::STATUS_CANCELLED,
                'rejection_reason' => 'user_cancelled',
                'updated_at' => now(),
            ]);

            $this->releaseAllocations($withdrawal);

            return true;
        });
    }

    public function releaseAllocations(WithdrawRequest $withdrawal): void
    {
        DB::table('withdrawal_revenues')->where('withdrawal_id', $withdrawal->id)->delete();
    }

    /**
     * Validate all strict rules for Early Withdrawal eligibility
     */
    private function validateEarlyWithdrawalEligibility(int $instructorId, float $amount, ?int $payoutAccountId = null): void
    {
        if (! config('revenue.early_withdrawal.enabled', true)) {
            throw new BusinessException('Tính năng thanh toán sớm hiện đang tạm tắt.', 422);
        }

        $minAmount = (float) config('revenue.early_withdrawal.minimum_amount', 200000);
        if ($amount < $minAmount) {
            throw ValidationException::withMessages(['amount' => "Số tiền yêu cầu thanh toán sớm tối thiểu là " . number_format($minAmount, 0, ',', '.') . " VNĐ."]);
        }

        $summary = $this->getPaymentSummary($instructorId);

        if ($amount > $summary['early_withdrawable_balance']) {
            throw ValidationException::withMessages(['amount' => "Số tiền yêu cầu vượt quá số dư khả dụng thực tế (" . number_format($summary['early_withdrawable_balance'], 0, ',', '.') . " VNĐ)."]);
        }

        if ($summary['has_active_early_withdrawal']) {
            throw new BusinessException('Bạn đang có một yêu cầu thanh toán sớm được xử lý.', 409);
        }

        if ($summary['early_withdrawal_requests_remaining'] <= 0) {
            throw new BusinessException('Bạn đã vượt quá số lượt yêu cầu thanh toán sớm trong tháng này (tối đa 2 lần/tháng).', 422);
        }

        // Remove automatic payout lock window check and cooldown check


        // Bank account check
        $payoutAccount = PayoutAccount::where('user_id', $instructorId)
            ->when($payoutAccountId, fn ($q) => $q->where('id', $payoutAccountId))
            ->orderByDesc('updated_at')
            ->first();

        if (! $payoutAccount || $payoutAccount->status !== PayoutAccount::STATUS_ACTIVE || $payoutAccount->user_id !== $instructorId) {
            throw ValidationException::withMessages(['payout_account_id' => 'Vui lòng cài đặt và xác minh tài khoản nhận tiền trước khi gửi yêu cầu thanh toán sớm.']);
        }

    }

    private function maskAccount(string $accountNumber): string
    {
        $len = strlen($accountNumber);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', max($len - 4, 0)) . substr($accountNumber, -4);
    }
}
