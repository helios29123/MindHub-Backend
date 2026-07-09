<?php
namespace App\Services\Instructor;
use App\Exceptions\BusinessException;
use App\Models\PayoutAccount;
use App\Models\WithdrawRequest;
use App\Repositories\Instructor\InstructorWithdrawalRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
final class InstructorWithdrawalService
{
    public function __construct(
        private readonly InstructorWithdrawalRepository $withdrawals,
        private readonly DatabaseManager $database
    ) {
    }
    /**
     * @throws AuthenticationException
     */
    public function summary(?object $authUser): array
    {
        $instructorId = $this->instructorId($authUser);
        $availableRevenue = $this->withdrawals->availableRevenueAmount($instructorId);
        $pendingWithdraw = $this->withdrawals->pendingWithdrawAmount($instructorId);
        $paidWithdraw = $this->withdrawals->paidWithdrawAmount($instructorId);
        $availableBalance = max(0, $availableRevenue - $pendingWithdraw);
        $payoutAccount = $this->withdrawals->activePayoutAccount($instructorId);
        $summary = [
            'available_revenue' => $availableRevenue,
            'pending_withdraw_amount' => $pendingWithdraw,
            'paid_withdraw_amount' => $paidWithdraw,
            'available_balance' => $availableBalance,
            'can_create_withdrawal' => $availableBalance > 0 && $payoutAccount !== null,
            'payout_account' => $payoutAccount,
        ];
        if ($payoutAccount === null) {
            $summary['notice'] = 'Bạn cần thêm tài khoản nhận tiền trước khi tạo yêu cầu rút.';
        }
        return $summary;
    }
    /**
     * @throws AuthenticationException
     */
    public function paginate(?object $authUser, array $filters): LengthAwarePaginator
    {
        return $this->withdrawals->paginateWithdrawals(
            $this->instructorId($authUser),
            $filters
        );
    }
    /**
     * @throws AuthenticationException
     */
    public function show(?object $authUser, int $id): WithdrawRequest
    {
        $instructorId = $this->instructorId($authUser);
        $withdrawal = $this->withdrawals->findOwnedWithdrawal($id, $instructorId);
        if ($withdrawal === null) {
            throw new BusinessException('Không tìm thấy yêu cầu rút tiền hoặc bạn không có quyền xem.', 404);
        }
        return $withdrawal;
    }
    /**
     * @throws AuthenticationException
     */
    public function store(?object $authUser, array $data): WithdrawRequest
    {
        $instructorId = $this->instructorId($authUser);
        $amount = round((float) ($data['amount'] ?? 0), 2);
        $payoutAccountId = (int) ($data['payout_account_id'] ?? 0);
        if ($amount <= 0) {
            throw new BusinessException('Số tiền rút không hợp lệ.', 422, [
                'amount' => ['Số tiền rút phải lớn hơn 0.'],
            ]);
        }
        return $this->database->transaction(function () use ($instructorId, $amount, $payoutAccountId): WithdrawRequest {
            $payoutAccount = $this->withdrawals->findPayoutAccountForUpdate($payoutAccountId, $instructorId);
            if ($payoutAccount === null) {
                throw new BusinessException('Tài khoản nhận tiền không hợp lệ.', 404);
            }
            if ($payoutAccount->status !== PayoutAccount::STATUS_ACTIVE) {
                throw new BusinessException('Tài khoản nhận tiền chưa hợp lệ.', 422, [
                    'payout_account_id' => ['Tài khoản nhận tiền phải đang hoạt động.'],
                ]);
            }
            $availableRevenue = $this->withdrawals->availableRevenueAmountForUpdate($instructorId);
            $pendingWithdraw = $this->withdrawals->pendingWithdrawAmountForUpdate($instructorId);
            $availableBalance = max(0, $availableRevenue - $pendingWithdraw);
            if ($amount > $availableBalance) {
                throw new BusinessException('Số dư có thể rút không đủ.', 409, [
                    'amount' => ['Số tiền yêu cầu vượt quá số dư có thể rút.'],
                ]);
            }
            return $this->withdrawals->createWithdrawRequest([
                'user_id' => $instructorId,
                'payout_account_id' => (int) $payoutAccount->id,
                'amount' => $amount,
                'status' => WithdrawRequest::STATUS_PENDING,
                'requested_at' => now(),
                'approved_at' => null,
                'paid_at' => null,
                'rejected_reason' => null,
                'account_number_snapshot' => $payoutAccount->account_number,
                'account_name_snapshot' => $payoutAccount->account_name,
            ]);
        });
    }
    /**
     * @throws AuthenticationException
     */
    public function payoutAccounts(?object $authUser, array $filters): Collection
    {
        $status = $filters['status'] ?? null;
        return $this->withdrawals->payoutAccounts(
            $this->instructorId($authUser),
            $status
        );
    }
    /**
     * @throws AuthenticationException
     */
    private function instructorId(?object $authUser): int
    {
        if ($authUser === null || empty($authUser->id)) {
            throw new AuthenticationException('Unauthenticated.');
        }
        return (int) $authUser->id;
    }
}