<?php

namespace App\Services\Payout;

use App\Exceptions\BusinessException;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\User;
use App\Models\UserOtp;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EarlyWithdrawalService
{
    public function getPaymentSummary(int $instructorId): array
    {
        app(\App\Services\Payment\RevenueShareService::class)
            ->syncMissingPaidOrderRevenues();

        $totalRevenue = (float) Revenue::query()
            ->where('instructor_id', $instructorId)
            ->sum('instructor_amount');

        $reservedBalance = (float) DB::table('withdrawal_revenues')
            ->join(
                'withdraw_requests',
                'withdraw_requests.id',
                '=',
                'withdrawal_revenues.withdrawal_id'
            )
            ->where('withdraw_requests.user_id', $instructorId)
            ->whereIn('withdraw_requests.status', [
                WithdrawRequest::STATUS_PENDING,
                WithdrawRequest::STATUS_APPROVED,
                WithdrawRequest::STATUS_PROCESSING,
                WithdrawRequest::STATUS_MANUAL_REQUIRED,
                WithdrawRequest::STATUS_PAID,
            ])
            ->sum('withdrawal_revenues.allocated_amount');

        $availableBalance = max($totalRevenue - $reservedBalance, 0);

        $totalPaid = (float) WithdrawRequest::query()
            ->where('user_id', $instructorId)
            ->where('status', WithdrawRequest::STATUS_PAID)
            ->sum('amount');

        $blockedAmount = (float) WithdrawRequest::query()
            ->where('user_id', $instructorId)
            ->where('status', WithdrawRequest::STATUS_MANUAL_REQUIRED)
            ->sum('amount');

        $minimumWithdrawal = (float) config(
            'revenue.early_withdrawal.minimum_amount',
            200000
        );

        $hasActiveWithdrawal = WithdrawRequest::query()
            ->where('user_id', $instructorId)
            ->whereIn('status', [
                WithdrawRequest::STATUS_PENDING,
                WithdrawRequest::STATUS_APPROVED,
                WithdrawRequest::STATUS_PROCESSING,
                WithdrawRequest::STATUS_MANUAL_REQUIRED,
            ])
            ->exists();

        $payoutAccount = PayoutAccount::query()
            ->where('user_id', $instructorId)
            ->whereIn('status', [PayoutAccount::STATUS_VERIFIED, 'active'])
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->first();

        $payoutAccountVerified = $payoutAccount !== null;

        $pendingWithdrawAmount = (float) WithdrawRequest::query()
            ->where('user_id', $instructorId)
            ->whereIn('status', [
                WithdrawRequest::STATUS_PENDING,
                WithdrawRequest::STATUS_APPROVED,
                WithdrawRequest::STATUS_PROCESSING,
                WithdrawRequest::STATUS_MANUAL_REQUIRED,
            ])
            ->sum('amount');

        $canCreateWithdrawal =
            $payoutAccountVerified
            && $availableBalance >= $minimumWithdrawal
            && ! $hasActiveWithdrawal;

        $notice = null;
        $blockedReason = null;

        if (! $payoutAccountVerified) {
            $notice = 'Bạn cần thêm và xác minh tài khoản nhận tiền trước khi tạo yêu cầu rút.';
            $blockedReason = 'Bạn chưa có tài khoản nhận tiền đã xác minh.';
        } elseif ($availableBalance < $minimumWithdrawal) {
            $blockedReason = 'Số dư khả dụng chưa đạt mức tối thiểu.';
        } elseif ($hasActiveWithdrawal) {
            $blockedReason = 'Bạn đang có một yêu cầu rút tiền được xử lý.';
        }

        return [
            'page_title' => 'Rút tiền giảng viên',
            'pending_revenue' => 0.0,
            'available_revenue' => round($availableBalance, 2),
            'available_balance' => round($availableBalance, 2),
            'reserved_balance' => round($reservedBalance, 2),
            'pending_withdraw_amount' => round($pendingWithdrawAmount, 2),
            'early_withdrawable_balance' => round($availableBalance, 2),
            'total_paid' => round($totalPaid, 2),
            'paid_withdraw_amount' => round($totalPaid, 2),
            'blocked_amount' => round($blockedAmount, 2),
            'minimum_payout' => $minimumWithdrawal,
            'minimum_early_withdrawal' => $minimumWithdrawal,
            'has_active_early_withdrawal' => $hasActiveWithdrawal,
            'can_create_withdrawal' => $canCreateWithdrawal,
            'notice' => $notice,
            'early_withdrawal_requests_remaining' => null,
            'next_early_withdrawal_available_at' => null,
            'payout_account_verified' => $payoutAccountVerified,
            'blocked_reason' => $blockedReason,
            'payout_account' => $payoutAccount,
        ];
    }

    public function requestOtp(
        int $instructorId,
        float $amount,
        ?int $payoutAccountId = null
    ): array {
        $this->validateWithdrawalEligibility(
            $instructorId,
            $amount,
            $payoutAccountId
        );

        $user = User::findOrFail($instructorId);

        $otpCode = (string) random_int(100000, 999999);
        $expiresInMinutes = (int) config(
            'revenue.early_withdrawal.otp_expires_minutes',
            5
        );
        $resendSeconds = (int) config(
            'revenue.early_withdrawal.otp_resend_seconds',
            60
        );

        UserOtp::query()
            ->where('user_id', $instructorId)
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

        try {
            Mail::raw(
                "Mã OTP xác thực yêu cầu rút tiền MindHub của bạn là: {$otpCode}. "
                . "Mã có hiệu lực trong {$expiresInMinutes} phút.",
                function ($message) use ($user): void {
                    $message
                        ->to($user->email)
                        ->subject('[MindHub] Mã OTP xác nhận rút tiền');
                }
            );
        } catch (\Throwable $e) {
        }

        $parts = explode('@', $user->email);
        $maskedEmail =
            substr($parts[0] ?? '', 0, 2)
            . '****@'
            . ($parts[1] ?? 'example.com');

        return [
            'masked_email' => $maskedEmail,
            'expires_in' => $expiresInMinutes * 60,
            'resend_after' => $resendSeconds,
        ];
    }

    public function createEarlyWithdrawal(
        int $instructorId,
        float $amount,
        ?int $payoutAccountId = null,
        ?string $otpCode = null
    ): WithdrawRequest {
        $otpRecord = null;

        if ($otpCode !== null && $otpCode !== '') {
            $otpRecord = UserOtp::query()
                ->where('user_id', $instructorId)
                ->where('purpose', 'early_withdrawal')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->orderByDesc('id')
                ->first();

            if (! $otpRecord) {
                throw new BusinessException(
                    'Mã OTP đã hết hạn hoặc không tồn tại. Vui lòng lấy mã mới.',
                    422
                );
            }

            $maxAttempts = (int) config(
                'revenue.early_withdrawal.otp_max_attempts',
                5
            );

            if ((int) $otpRecord->attempts >= $maxAttempts) {
                throw new BusinessException(
                    'Bạn đã nhập sai OTP quá số lần cho phép. Vui lòng lấy mã OTP mới.',
                    422
                );
            }

            if (! Hash::check($otpCode, $otpRecord->code_hash)) {
                $otpRecord->increment('attempts');

                throw new BusinessException(
                    'Mã OTP không chính xác.',
                    422
                );
            }
        } else {
            throw new BusinessException(
                'Vui lòng nhập mã OTP xác thực 6 chữ số.',
                422
            );
        }

        return DB::transaction(function () use (
            $instructorId,
            $amount,
            $payoutAccountId,
            $otpRecord
        ): WithdrawRequest {
            $this->validateWithdrawalEligibility(
                $instructorId,
                $amount,
                $payoutAccountId
            );

            $payoutAccount = PayoutAccount::query()
                ->where('user_id', $instructorId)
                ->whereIn('status', [PayoutAccount::STATUS_VERIFIED, 'active'])
                ->when(
                    $payoutAccountId,
                    fn ($query) => $query->where('id', $payoutAccountId)
                )
                ->orderByDesc('is_default')
                ->orderByDesc('updated_at')
                ->lockForUpdate()
                ->first();

            if (! $payoutAccount) {
                throw new BusinessException(
                    'Không tìm thấy tài khoản nhận tiền đã xác minh.',
                    422
                );
            }

            $summary = $this->getPaymentSummary($instructorId);
            $availableBefore = (float) $summary['available_balance'];

            if ($amount > $availableBefore) {
                throw ValidationException::withMessages([
                    'amount' => 'Số tiền yêu cầu vượt quá số dư khả dụng.',
                ]);
            }

            $withdrawal = WithdrawRequest::create([
                'user_id' => $instructorId,
                'payout_account_id' => $payoutAccount->id,
                'amount' => $amount,
                'status' => WithdrawRequest::STATUS_PENDING,
                'requested_at' => now(),
                'account_number_snapshot' => $payoutAccount->account_number,
                'account_name_snapshot' => $payoutAccount->account_name,
                'bank_name_snapshot' => $payoutAccount->provider,
                'available_balance_before' => $availableBefore,
                'available_balance_after' => max($availableBefore - $amount, 0),
                'payout_provider' => $payoutAccount->provider,
            ]);

            $allocatedQuery = DB::table('withdrawal_revenues')
                ->select(
                    'revenue_id',
                    DB::raw('SUM(allocated_amount) as total_allocated')
                )
                ->join(
                    'withdraw_requests',
                    'withdraw_requests.id',
                    '=',
                    'withdrawal_revenues.withdrawal_id'
                )
                ->whereIn('withdraw_requests.status', [
                    WithdrawRequest::STATUS_PENDING,
                    WithdrawRequest::STATUS_APPROVED,
                    WithdrawRequest::STATUS_PROCESSING,
                    WithdrawRequest::STATUS_MANUAL_REQUIRED,
                    WithdrawRequest::STATUS_PAID,
                ])
                ->groupBy('revenue_id');

            $availableRevenues = Revenue::query()
                ->where('instructor_id', $instructorId)
                ->leftJoinSub(
                    $allocatedQuery,
                    'allocated',
                    'revenues.id',
                    '=',
                    'allocated.revenue_id'
                )
                ->whereRaw(
                    'COALESCE(allocated.total_allocated, 0) < revenues.instructor_amount'
                )
                ->select(
                    'revenues.*',
                    DB::raw(
                        'COALESCE(allocated.total_allocated, 0) as already_allocated'
                    )
                )
                ->orderBy('revenues.earned_at')
                ->orderBy('revenues.id')
                ->lockForUpdate()
                ->get();

            $remainingToAllocate = $amount;

            foreach ($availableRevenues as $revenue) {
                if ($remainingToAllocate <= 0) {
                    break;
                }

                $unallocatedAmount = max(
                    (float) $revenue->instructor_amount
                    - (float) $revenue->already_allocated,
                    0
                );

                if ($unallocatedAmount <= 0) {
                    continue;
                }

                $allocationAmount = min(
                    $remainingToAllocate,
                    $unallocatedAmount
                );

                DB::table('withdrawal_revenues')->insert([
                    'withdrawal_id' => $withdrawal->id,
                    'revenue_id' => $revenue->id,
                    'allocated_amount' => $allocationAmount,
                    'created_at' => now(),
                ]);

                $remainingToAllocate -= $allocationAmount;
            }

            if (round($remainingToAllocate, 2) > 0) {
                throw new BusinessException(
                    'Không đủ revenue khả dụng để phân bổ cho yêu cầu rút tiền.',
                    409
                );
            }

            if ($otpRecord !== null) {
                $otpRecord->update(['used_at' => now()]);
            }

            return $withdrawal->fresh([
                'payoutAccount',
                'revenues',
            ]);
        });
    }

    public function cancelEarlyWithdrawal(
        int $instructorId,
        int $withdrawalId
    ): bool {
        return DB::transaction(function () use (
            $instructorId,
            $withdrawalId
        ): bool {
            $withdrawal = WithdrawRequest::query()
                ->where('user_id', $instructorId)
                ->where('id', $withdrawalId)
                ->lockForUpdate()
                ->first();

            if (! $withdrawal) {
                throw new BusinessException(
                    'Không tìm thấy yêu cầu rút tiền.',
                    404
                );
            }

            if ($withdrawal->status !== WithdrawRequest::STATUS_PENDING) {
                throw new BusinessException(
                    'Chỉ có thể hủy yêu cầu rút tiền ở trạng thái chờ duyệt.',
                    422
                );
            }

            $withdrawal->update([
                'status' => WithdrawRequest::STATUS_CANCELLED,
                'rejected_reason' => 'user_cancelled',
            ]);

            $this->releaseAllocations($withdrawal);

            return true;
        });
    }

    public function releaseAllocations(WithdrawRequest $withdrawal): void
    {
        DB::table('withdrawal_revenues')
            ->where('withdrawal_id', $withdrawal->id)
            ->delete();
    }

    private function validateWithdrawalEligibility(
        int $instructorId,
        float $amount,
        ?int $payoutAccountId = null
    ): void {
        if (! config('revenue.early_withdrawal.enabled', true)) {
            throw new BusinessException(
                'Tính năng rút tiền hiện đang tạm tắt.',
                422
            );
        }

        $minimumAmount = (float) config(
            'revenue.early_withdrawal.minimum_amount',
            200000
        );

        if ($amount < $minimumAmount) {
            throw ValidationException::withMessages([
                'amount' =>
                    'Số tiền yêu cầu rút tối thiểu là '
                    . number_format($minimumAmount, 0, ',', '.')
                    . ' VNĐ.',
            ]);
        }

        $summary = $this->getPaymentSummary($instructorId);

        if ($amount > (float) $summary['available_balance']) {
            throw ValidationException::withMessages([
                'amount' =>
                    'Số tiền yêu cầu vượt quá số dư khả dụng thực tế ('
                    . number_format(
                        (float) $summary['available_balance'],
                        0,
                        ',',
                        '.'
                    )
                    . ' VNĐ).',
            ]);
        }

        if ((bool) $summary['has_active_early_withdrawal']) {
            throw new BusinessException(
                'Bạn đang có một yêu cầu rút tiền được xử lý.',
                409
            );
        }

        $payoutAccount = PayoutAccount::query()
            ->where('user_id', $instructorId)
            ->whereIn('status', [PayoutAccount::STATUS_VERIFIED, 'active'])
            ->when(
                $payoutAccountId,
                fn ($query) => $query->where('id', $payoutAccountId)
            )
            ->first();

        if (! $payoutAccount) {
            throw ValidationException::withMessages([
                'payout_account_id' =>
                    'Vui lòng cài đặt và xác minh tài khoản nhận tiền trước khi gửi yêu cầu rút tiền.',
            ]);
        }
    }
}
