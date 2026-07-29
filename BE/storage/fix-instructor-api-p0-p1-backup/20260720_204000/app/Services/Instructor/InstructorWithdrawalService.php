<?php

namespace App\Services\Instructor;

use App\Repositories\Instructor\InstructorWithdrawalRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InstructorWithdrawalService
{
    public function __construct(
        private readonly InstructorWithdrawalRepository $repository
    ) {
    }

    public function summary(int $instructorId, array $filters = []): array
    {
        return $this->repository->getSummary($instructorId);
    }

    public function paginate(int $instructorId, array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateWithdrawals($instructorId, $filters);
    }
    public function show(int $instructorId, int $withdrawalId): ?array
    {
        return $this->repository->getWithdrawalDetail($instructorId, $withdrawalId);
    }
    public function store(int $instructorId, array $data): ?array
    {
        $payoutAccountId = (int) $data['payout_account_id'];
        $amount = (float) $data['amount'];

        $payoutAccount = \Illuminate\Support\Facades\DB::table('payout_accounts')
            ->where('id', $payoutAccountId)
            ->where('user_id', $instructorId)
            ->whereNull('deleted_at')
            ->first();

        if (!$payoutAccount) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Không tìm thấy tài khoản nhận tiền.');
        }

        if ($payoutAccount->status !== 'active' && $payoutAccount->status !== 'verified') {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException('Tài khoản nhận tiền chưa được kích hoạt hoặc xác minh.');
        }

        if ($amount < 200000) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException('Số tiền rút tối thiểu là 200,000đ.');
        }

        $summary = $this->summary($instructorId);
        $withdrawableBalance = (float) $summary['available_balance'];

        if ($amount > $withdrawableBalance) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException('Số tiền rút vượt quá số dư khả dụng.');
        }

        $payload = [
            'amount' => $amount,
            'account_number' => $payoutAccount->account_number,
            'account_name' => $payoutAccount->account_name,
            'payout_account_id' => $payoutAccountId,
        ];

        return $this->repository->createWithdrawal($instructorId, $payload);
    }
    public function payoutAccounts(int $instructorId): ?array
    {
        return $this->repository->getPayoutAccount($instructorId);
    }
    public function cancel(int $instructorId, int $withdrawalId): bool
    {
        return $this->repository->cancelWithdrawal($instructorId, $withdrawalId);
    }
}
