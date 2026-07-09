<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
final class InstructorWithdrawalApiTest extends TestCase
{
    use DatabaseTransactions;
    private User $instructor;
    private User $otherInstructor;
    private User $learner;
    private int $courseId;
    private int $activeAccountId;
    private int $inactiveAccountId;
    private int $otherAccountId;
    private int $pendingWithdrawalId;
    private int $approvedWithdrawalId;
    private int $rejectedWithdrawalId;
    private int $paidWithdrawalId;
    private int $cancelledWithdrawalId;
    private int $otherWithdrawalId;
    private int $sequence = 1;
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $suffix = str_replace('.', '-', uniqid('withdraw_', true));
        $this->instructor = $this->createUser('Withdraw Instructor', 'withdraw-instructor-' . $suffix . '@mindhub.test', 'instructor');
        $this->otherInstructor = $this->createUser('Other Withdraw Instructor', 'other-withdraw-instructor-' . $suffix . '@mindhub.test', 'instructor');
        $this->learner = $this->createUser('Withdraw Learner', 'withdraw-learner-' . $suffix . '@mindhub.test', 'learner');
        $this->courseId = $this->createCourse((int) $this->instructor->id, 'Withdraw Course ' . $suffix, 'withdraw-course-' . $suffix);
        $this->activeAccountId = $this->createPayoutAccount((int) $this->instructor->id, [
            'provider' => 'bank',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890001',
            'status' => 'active',
        ]);
        $this->inactiveAccountId = $this->createPayoutAccount((int) $this->instructor->id, [
            'provider' => 'bank',
            'account_name' => 'NGUYEN VAN B',
            'account_number' => '9876543210002',
            'status' => 'inactive',
        ]);
        $this->otherAccountId = $this->createPayoutAccount((int) $this->otherInstructor->id, [
            'provider' => 'bank',
            'account_name' => 'OTHER USER',
            'account_number' => '1111222233334',
            'status' => 'active',
        ]);
        /*
         * Revenue:
         * available = 10,000,000
         * pending/withdrawn/cancelled không tính vào số dư rút.
         */
        $this->createRevenue((int) $this->instructor->id, $this->courseId, 6000000, 'available');
        $this->createRevenue((int) $this->instructor->id, $this->courseId, 4000000, 'available');
        $this->createRevenue((int) $this->instructor->id, $this->courseId, 3000000, 'pending');
        $this->createRevenue((int) $this->instructor->id, $this->courseId, 2000000, 'withdrawn');
        $this->createRevenue((int) $this->instructor->id, $this->courseId, 1000000, 'cancelled');
        $this->createRevenue((int) $this->otherInstructor->id, $this->courseId, 9999999, 'available');
        /*
         * Withdraw:
         * pending + approved = 3,000,000
         * paid = 5,000,000
         * rejected/cancelled không trừ số dư.
         * available_balance = 10,000,000 - 3,000,000 = 7,000,000
         */
        $this->pendingWithdrawalId = $this->createWithdrawal((int) $this->instructor->id, $this->activeAccountId, 1000000, 'pending', now()->subDays(5));
        $this->approvedWithdrawalId = $this->createWithdrawal((int) $this->instructor->id, $this->activeAccountId, 2000000, 'approved', now()->subDays(4), approvedAt: now()->subDays(3));
        $this->rejectedWithdrawalId = $this->createWithdrawal((int) $this->instructor->id, $this->activeAccountId, 3000000, 'rejected', now()->subDays(3), rejectedReason: 'Sai thông tin tài khoản.');
        $this->paidWithdrawalId = $this->createWithdrawal((int) $this->instructor->id, $this->activeAccountId, 5000000, 'paid', now()->subDays(2), approvedAt: now()->subDays(2), paidAt: now()->subDay());
        $this->cancelledWithdrawalId = $this->createWithdrawal((int) $this->instructor->id, $this->activeAccountId, 700000, 'cancelled', now()->subDay());
        $this->otherWithdrawalId = $this->createWithdrawal((int) $this->otherInstructor->id, $this->otherAccountId, 999000, 'pending', now());
    }
    public function test_instructor_can_get_withdrawal_summary(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/summary');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.available_revenue', 10000000)
            ->assertJsonPath('data.pending_withdraw_amount', 3000000)
            ->assertJsonPath('data.paid_withdraw_amount', 5000000)
            ->assertJsonPath('data.available_balance', 7000000)
            ->assertJsonPath('data.can_create_withdrawal', true)
            ->assertJsonPath('data.payout_account.account_number_masked', '*********0001');
    }
    public function test_summary_returns_notice_when_no_active_payout_account(): void
    {
        DB::table('payout_accounts')
            ->where('user_id', $this->instructor->id)
            ->update([
                'status' => 'inactive',
                'updated_at' => now(),
            ]);
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/summary');
        $response->assertOk()
            ->assertJsonPath('data.can_create_withdrawal', false)
            ->assertJsonPath('data.payout_account', null)
            ->assertJsonPath('data.notice', 'Bạn cần thêm tài khoản nhận tiền trước khi tạo yêu cầu rút.');
    }
    public function test_instructor_can_list_withdrawals(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals?per_page=20');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 5);
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($this->pendingWithdrawalId, $ids);
        $this->assertContains($this->approvedWithdrawalId, $ids);
        $this->assertContains($this->rejectedWithdrawalId, $ids);
        $this->assertContains($this->paidWithdrawalId, $ids);
        $this->assertContains($this->cancelledWithdrawalId, $ids);
        $this->assertNotContains($this->otherWithdrawalId, $ids);
    }
    public function test_instructor_can_filter_withdrawals_by_status(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals?status=paid');
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $this->paidWithdrawalId)
            ->assertJsonPath('data.0.status_label', 'Đã thanh toán');
    }
    public function test_instructor_can_filter_withdrawals_by_requested_date(): void
    {
        $date = now()->subDays(4)->format('Y-m-d');
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals?date_from=' . $date . '&date_to=' . $date);
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $this->approvedWithdrawalId);
    }
    public function test_index_rejects_invalid_status(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals?status=processing');
        $response->assertUnprocessable();
    }
    public function test_index_rejects_invalid_date_range(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals?date_from=2026-07-10&date_to=2026-07-01');
        $response->assertUnprocessable();
    }
    public function test_instructor_can_show_withdrawal_detail(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/' . $this->approvedWithdrawalId);
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->approvedWithdrawalId)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.account.account_number_snapshot_masked', '*********0001')
            ->assertJsonPath('data.timeline.0.key', 'requested')
            ->assertJsonPath('data.timeline.1.key', 'approved');
    }
    public function test_show_rejects_other_instructor_withdrawal(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/' . $this->otherWithdrawalId);
        $response->assertNotFound();
    }
    public function test_show_returns_rejected_reason(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/' . $this->rejectedWithdrawalId);
        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejected_reason', 'Sai thông tin tài khoản.');
    }
    public function test_instructor_can_create_withdrawal_request(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->activeAccountId,
                'amount' => 2500000,
            ]);
        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.amount', 2500000)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.status_label', 'Chờ xử lý')
            ->assertJsonPath('data.account.account_number_snapshot_masked', '*********0001');
        $this->assertDatabaseHas('withdraw_requests', [
            'user_id' => $this->instructor->id,
            'payout_account_id' => $this->activeAccountId,
            'amount' => 2500000,
            'status' => 'pending',
            'account_name_snapshot' => 'NGUYEN VAN A',
            'account_number_snapshot' => '1234567890001',
        ]);
    }
    public function test_create_withdrawal_reduces_available_balance_in_summary(): void
    {
        $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->activeAccountId,
                'amount' => 2000000,
            ])
            ->assertCreated();
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/summary');
        $response->assertOk()
            ->assertJsonPath('data.pending_withdraw_amount', 5000000)
            ->assertJsonPath('data.available_balance', 5000000);
    }
    public function test_create_rejects_amount_greater_than_available_balance(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->activeAccountId,
                'amount' => 8000000,
            ]);
        $response->assertStatus(409);
    }
    public function test_create_rejects_inactive_payout_account(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->inactiveAccountId,
                'amount' => 1000000,
            ]);
        $response->assertUnprocessable();
    }
    public function test_create_rejects_other_user_payout_account(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->otherAccountId,
                'amount' => 1000000,
            ]);
        $response->assertNotFound();
    }
    public function test_create_rejects_client_controlled_fields(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->activeAccountId,
                'amount' => 1000000,
                'status' => 'paid',
                'user_id' => $this->otherInstructor->id,
                'account_number_snapshot' => 'HACKED',
            ]);
        $response->assertUnprocessable();
    }
    public function test_create_requires_positive_amount(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->activeAccountId,
                'amount' => 0,
            ]);
        $response->assertUnprocessable();
    }
    public function test_instructor_can_get_active_payout_accounts_by_default(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/payout-accounts');
        $response->assertOk()
            ->assertJsonPath('success', true);
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($this->activeAccountId, $ids);
        $this->assertNotContains($this->inactiveAccountId, $ids);
        $this->assertNotContains($this->otherAccountId, $ids);
    }
    public function test_instructor_can_get_inactive_payout_accounts_when_filtered(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/payout-accounts?status=inactive');
        $response->assertOk();
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($this->inactiveAccountId, $ids);
        $this->assertNotContains($this->activeAccountId, $ids);
    }
    public function test_payout_accounts_reject_invalid_status(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/payout-accounts?status=pending');
        $response->assertUnprocessable();
    }
    public function test_summary_only_subtracts_pending_and_approved_withdrawals(): void
    {
        /*
         * Data hiện có trong setUp:
         * available revenue = 10,000,000
         * pending withdraw = 1,000,000
         * approved withdraw = 2,000,000
         * paid withdraw = 5,000,000
         * rejected withdraw = 3,000,000
         * cancelled withdraw = 700,000
         *
         * Công thức đúng:
         * available_balance = available revenue - pending - approved
         * available_balance = 10,000,000 - 1,000,000 - 2,000,000 = 7,000,000
         *
         * paid/rejected/cancelled không được trừ vào số dư có thể rút.
         */
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/summary');
        $response->assertOk()
            ->assertJsonPath('data.available_revenue', 10000000)
            ->assertJsonPath('data.pending_withdraw_amount', 3000000)
            ->assertJsonPath('data.paid_withdraw_amount', 5000000)
            ->assertJsonPath('data.available_balance', 7000000);
    }
    public function test_summary_ignores_non_available_revenues(): void
    {
        /*
         * setUp có tạo thêm revenue status pending, withdrawn, cancelled.
         * Các revenue này không được tính vào available_revenue.
         */
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/summary');
        $response->assertOk()
            ->assertJsonPath('data.available_revenue', 10000000)
            ->assertJsonPath('data.available_balance', 7000000);
    }
    public function test_create_allows_amount_equal_to_available_balance(): void
    {
        /*
         * Số dư có thể rút ban đầu = 7,000,000.
         * Rút đúng bằng số dư thì phải cho tạo.
         */
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->activeAccountId,
                'amount' => 7000000,
            ]);
        $response->assertCreated()
            ->assertJsonPath('data.amount', 7000000)
            ->assertJsonPath('data.status', 'pending');
        $summary = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/summary');
        $summary->assertOk()
            ->assertJsonPath('data.pending_withdraw_amount', 10000000)
            ->assertJsonPath('data.available_balance', 0)
            ->assertJsonPath('data.can_create_withdrawal', false);
    }
    public function test_created_withdrawal_has_pending_status_and_no_approved_or_paid_time(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->activeAccountId,
                'amount' => 1500000,
            ]);
        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.status_label', 'Chờ xử lý')
            ->assertJsonPath('data.approved_at', null)
            ->assertJsonPath('data.paid_at', null);
        $this->assertNotNull($response->json('data.requested_at'));
        $this->assertDatabaseHas('withdraw_requests', [
            'id' => $response->json('data.id'),
            'user_id' => $this->instructor->id,
            'status' => 'pending',
            'approved_at' => null,
            'paid_at' => null,
        ]);
    }
    public function test_withdrawal_keeps_payout_account_snapshot_after_account_changes(): void
    {
        /*
         * Khi tạo yêu cầu rút tiền, hệ thống phải snapshot số tài khoản/tên tài khoản.
         * Nếu sau đó payout account bị sửa, lịch sử withdrawal cũ vẫn giữ thông tin snapshot cũ.
         */
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'payout_account_id' => $this->activeAccountId,
                'amount' => 1000000,
            ]);
        $response->assertCreated();
        $withdrawalId = (int) $response->json('data.id');
        DB::table('payout_accounts')
            ->where('id', $this->activeAccountId)
            ->update([
                'account_name' => 'CHANGED ACCOUNT NAME',
                'account_number' => '9999999999999',
                'updated_at' => now(),
            ]);
        $detail = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/' . $withdrawalId);
        $detail->assertOk()
            ->assertJsonPath('data.account.account_name_snapshot', 'NGUYEN VAN A')
            ->assertJsonPath('data.account.account_number_snapshot_masked', '*********0001');
        $this->assertDatabaseHas('withdraw_requests', [
            'id' => $withdrawalId,
            'account_name_snapshot' => 'NGUYEN VAN A',
            'account_number_snapshot' => '1234567890001',
        ]);
    }
    public function test_withdrawal_list_supports_pagination(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals?per_page=2&page=2');
        $response->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5);
        $this->assertCount(2, $response->json('data'));
    }
    public function test_withdrawal_list_status_all_returns_all_statuses(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals?status=all&per_page=20');
        $response->assertOk()
            ->assertJsonPath('meta.total', 5);
        $statuses = array_column($response->json('data'), 'status');
        $this->assertContains('pending', $statuses);
        $this->assertContains('approved', $statuses);
        $this->assertContains('rejected', $statuses);
        $this->assertContains('paid', $statuses);
        $this->assertContains('cancelled', $statuses);
    }
    public function test_show_paid_withdrawal_has_paid_timeline_completed(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/' . $this->paidWithdrawalId);
        $response->assertOk()
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.timeline.0.key', 'requested')
            ->assertJsonPath('data.timeline.0.completed', true)
            ->assertJsonPath('data.timeline.1.key', 'approved')
            ->assertJsonPath('data.timeline.1.completed', true)
            ->assertJsonPath('data.timeline.2.key', 'paid')
            ->assertJsonPath('data.timeline.2.completed', true);
    }
    public function test_show_cancelled_withdrawal_has_cancelled_timeline(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/withdrawals/' . $this->cancelledWithdrawalId);
        $response->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.timeline.3.key', 'cancelled')
            ->assertJsonPath('data.timeline.3.completed', true);
    }
    public function test_payout_account_masks_short_account_number(): void
    {
        $shortAccountId = $this->createPayoutAccount((int) $this->instructor->id, [
            'provider' => 'bank',
            'account_name' => 'SHORT ACCOUNT',
            'account_number' => '123',
            'status' => 'active',
        ]);
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/payout-accounts?status=active');
        $response->assertOk();
        $account = collect($response->json('data'))
            ->firstWhere('id', $shortAccountId);
        $this->assertNotNull($account);
        $this->assertSame('***', $account['account_number_masked']);
    }    private function createUser(string $fullName, string $email, string $role): User
    {
        return User::query()->create([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => null,
            'role' => $role,
            'status' => 'active',
            'locked' => false,
        ]);
    }
    private function createCourse(int $instructorId, string $title, string $slug): int
    {
        return DB::table('courses')->insertGetId([
            'instructor_id' => $instructorId,
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'Withdraw course',
            'description' => 'Withdraw course',
            'price' => 500000,
            'sale_price' => null,
            'level' => 'beginner',
            'language' => 'vi',
            'status' => 'published',
            'is_featured' => false,
            'total_duration_seconds' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }
    private function createOrder(int $courseId, float $amount): int
    {
        $data = [
            'user_id' => $this->learner->id,
            'course_id' => $courseId,
        ];
        if (Schema::hasColumn('orders', 'order_code')) {
            $data['order_code'] = 'WR-ORD-' . $this->sequence . '-' . random_int(1000, 9999);
        }
        if (Schema::hasColumn('orders', 'order_type')) {
            $data['order_type'] = 'course_purchase';
        }
        foreach (['price', 'price_snapshot', 'amount', 'final_amount'] as $column) {
            if (Schema::hasColumn('orders', $column)) {
                $data[$column] = $amount;
            }
        }
        if (Schema::hasColumn('orders', 'discount_amount')) {
            $data['discount_amount'] = 0;
        }
        if (Schema::hasColumn('orders', 'status')) {
            $data['status'] = 'paid';
        }
        if (Schema::hasColumn('orders', 'payment_status')) {
            $data['payment_status'] = 'paid';
        }
        if (Schema::hasColumn('orders', 'payment_method')) {
            $data['payment_method'] = 'manual';
        }
        if (Schema::hasColumn('orders', 'provider_transaction_id')) {
            $data['provider_transaction_id'] = 'WR-TXN-' . $this->sequence . '-' . random_int(1000, 9999);
        }
        if (Schema::hasColumn('orders', 'paid_at')) {
            $data['paid_at'] = now();
        }
        foreach (['coupon_id', 'credit_package_id', 'package_snapshot_name', 'package_snapshot_credits'] as $column) {
            if (Schema::hasColumn('orders', $column)) {
                $data[$column] = null;
            }
        }
        if (Schema::hasColumn('orders', 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn('orders', 'updated_at')) {
            $data['updated_at'] = now();
        }
        return DB::table('orders')->insertGetId($data);
    }
    private function createRevenue(int $instructorId, int $courseId, float $amount, string $status): int
    {
        $this->sequence++;
        $orderId = $this->createOrder($courseId, $amount);
        $data = [
            'instructor_id' => $instructorId,
            'course_id' => $courseId,
            'order_id' => $orderId,
            'gross_amount' => $amount,
            'instructor_amount' => $amount,
            'platform_fee_amount' => 0,
            'status' => $status,
            'earned_at' => now()->subDays(10)->addSeconds($this->sequence),
            'created_at' => now()->subDays(10)->addSeconds($this->sequence),
        ];
        if (Schema::hasColumn('revenues', 'updated_at')) {
            $data['updated_at'] = now();
        }
        return DB::table('revenues')->insertGetId($data);
    }
    private function createPayoutAccount(int $userId, array $overrides): int
    {
        $this->sequence++;
        $data = array_merge([
            'user_id' => $userId,
            'provider' => 'bank',
            'account_number' => '0000000000000',
            'account_name' => 'TEST ACCOUNT',
            'connected_at' => now(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ], $overrides);
        foreach (array_keys($data) as $column) {
            if (!Schema::hasColumn('payout_accounts', $column)) {
                unset($data[$column]);
            }
        }
        return DB::table('payout_accounts')->insertGetId($data);
    }
    private function createWithdrawal(
        int $userId,
        int $payoutAccountId,
        float $amount,
        string $status,
        mixed $requestedAt,
        mixed $approvedAt = null,
        mixed $paidAt = null,
        ?string $rejectedReason = null
    ): int {
        $this->sequence++;
        $account = DB::table('payout_accounts')
            ->where('id', $payoutAccountId)
            ->first();
        $data = [
            'user_id' => $userId,
            'payout_account_id' => $payoutAccountId,
            'amount' => $amount,
            'status' => $status,
            'requested_at' => $requestedAt,
            'approved_at' => $approvedAt,
            'paid_at' => $paidAt,
            'rejected_reason' => $rejectedReason,
            'provider_payout_id' => null,
            'account_number_snapshot' => $account?->account_number,
            'account_name_snapshot' => $account?->account_name,
            'created_at' => $requestedAt,
            'updated_at' => now()->addSeconds($this->sequence),
        ];
        foreach (array_keys($data) as $column) {
            if (!Schema::hasColumn('withdraw_requests', $column)) {
                unset($data[$column]);
            }
        }
        return DB::table('withdraw_requests')->insertGetId($data);
    }
}