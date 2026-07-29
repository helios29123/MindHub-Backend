<?php

namespace App\Services\Admin;

use App\Models\PayoutAccount;
use App\Models\User;
use App\Repositories\Admin\AdminPayoutAccountRepository;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Support\Facades\DB;

final class AdminPayoutAccountService
{
    public function __construct(private readonly AdminPayoutAccountRepository $repo, private readonly AdminNotificationService $notifications) {}
    public function paginate(array $filters)
    {
        return $this->repo->paginate($filters);
    }
    public function show(PayoutAccount $account): PayoutAccount
    {
        return $account->load('user');
    }
    public function approve(PayoutAccount $account, User $admin): PayoutAccount
    {
        $account->update(['status' => 'active', 'verified_at' => now()]);
        $this->notifications->audit($admin, 'payout_account.approve', $account);
        return $account->fresh('user');
    }
    public function reject(PayoutAccount $account, ?string $reason, User $admin): PayoutAccount
    {
        $account->update(['status' => 'rejected', 'rejected_reason' => $reason]);
        $this->notifications->audit($admin, 'payout_account.reject', $account, [], ['reason' => $reason]);
        return $account->fresh('user');
    }
    public function disable(PayoutAccount $account, ?string $reason, User $admin): PayoutAccount
    {
        $account->update(['status' => 'inactive', 'disabled_reason' => $reason]);
        $this->notifications->audit($admin, 'payout_account.disable', $account, [], ['reason' => $reason]);
        return $account->fresh('user');
    }
    public function logs(PayoutAccount $account): array
    {
        return DB::table('audit_logs')->where('auditable_type', PayoutAccount::class)->where('auditable_id', $account->id)->orderByDesc('created_at')->get()->toArray();
    }
}
