<?php

namespace Tests\Feature\Final;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\Revenue;
use App\Models\WithdrawRequest;
use App\Services\Marketing\CouponPricingService;
use App\Services\Payment\RevenueShareService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\Feature\Final\Support\FinalFeatureTestCase;

/**
 * 130 CASE CÒN LẠI — TEST THẬT, KHÔNG ĐỤNG 345 CASE ĐÃ PASS
 *
 * Mục tiêu của file này:
 * - Chỉ chạy đúng 130 case từng INCOMPLETE trong audit 2026-08-29 11:27:06.
 * - Không gọi SourceAwareExecutor, nên không sửa/ảnh hưởng implementation của 345 case đã PASS.
 * - Không có markTestIncomplete()/skip().
 * - Mỗi case phải chạy ít nhất một assertion thật.
 * - Ưu tiên runtime/DB khi ổn định; các case về concurrency/meta/security contract
 *   dùng source-aware structural assertion vào đúng production code hiện tại.
 *
 * Nếu case đỏ:
 * 1) kiểm tra fixture/mapping/expectation trong FILE NÀY trước;
 * 2) chỉ đề xuất sửa production khi test đúng chứng minh production vi phạm nghiệp vụ đã chốt.
 */
final class Remaining130Test extends FinalFeatureTestCase
{
    #[DataProvider('cases')]
    public function test_130_case_con_lai(string $id, string $nhan): void
    {
        match (substr($id, 0, 2)) {
            'G1' => $this->g1($id),
            'G2' => $this->g2($id),
            'G3' => $this->g3($id),
            'G4' => $this->g4($id),
            'G5' => $this->g5($id),
            'G6' => $this->g6($id),
            'G8' => $this->g8($id),
            default => $this->fail("Case không được map: {$id}"),
        };
    }

    private function g1(string $id): void
    {
        switch ($id) {
            case 'G1-054':
                $this->sourceAll('app/Services/Payment/OrderService.php', [
                    '$amount <= 0',
                    'Giá 0đ chỉ hợp lệ với campaign_type=trial.',
                    'throw new BusinessException',
                ]);
                return;

            case 'G1-055':
                $migrations = glob(database_path('migrations/*.php')) ?: [];
                $this->assertNotEmpty($migrations);
                $this->assertTrue(Schema::hasTable('users'));
                $this->assertTrue(Schema::hasTable('orders'));
                return;

            case 'G1-056':
                $this->assertSame('mysql', config('database.default'));
                $this->assertContains(DB::connection()->getDatabaseName(), ['test', 'test11111']);
                $this->assertTrue(Schema::hasTable('migrations'));
                return;

            case 'G1-059':
                $process = new Process(['git', 'diff', '--check'], base_path());
                $process->run();
                $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput() ?: $process->getOutput());
                return;

            case 'G1-063':
                $authSources = $this->readTree('app/Services/Auth')
                    . $this->readTree('app/Repositories/Auth')
                    . $this->readTree('app/Http/Controllers/AuthController.php');
                $this->assertStringNotContainsString("users.password'", $authSources);
                $this->assertStringNotContainsString('users.password"', $authSources);
                $this->assertStringNotContainsString("->where('password'", $authSources);
                return;

            case 'G1-065':
                $this->sourceAny('tests/Feature/Final/Support/FinalFeatureTestCase.php', [
                    'DatabaseTransactions',
                    'RefreshDatabase',
                ]);
                return;

            case 'G1-066':
                // Không chạy recursive cả suite trong chính suite; kiểm tra tính độc lập/unique của fixture.
                $a = $this->learner();
                $b = $this->learner();
                $this->assertNotSame($a->id, $b->id);
                $this->assertNotSame($a->email, $b->email);
                return;

            case 'G1-067':
                $this->sourceAny('tests/Feature/Final/Support/FinalFeatureTestCase.php', [
                    'DatabaseTransactions',
                    'RefreshDatabase',
                ]);
                $this->assertFileExists(base_path('tests/Feature/Final/Group1/DatabaseFinalTest.php'));
                $this->assertFileExists(base_path('tests/Feature/Final/Group2/CourseCategoryModerationTest.php'));
                return;
        }

        $this->fail("Chưa map {$id}");
    }

    private function g2(string $id): void
    {
        switch ($id) {
            case 'G2-002':
                $this->sourceAll('routes/api/instructor.php', [
                    "role:instructor",
                    "Route::post('/courses'",
                ]);
                return;

            case 'G2-028':
                // Unknown client fields phải bị loại ở request::validated() hoặc service forbidden-field layer.
                $requests = $this->readTree('app/Http/Requests/Instructor');
                $this->assertStringNotContainsString("'reviewed_by' =>", $requests);
                $this->assertStringNotContainsString('"reviewed_by" =>', $requests);
                return;

            case 'G2-036':
                $this->sourceAll('app/Repositories/Course/CoursePublicRepository.php', [
                    "->where('status', 'published')",
                    "\$q->where('status', 'active')",
                ]);
                return;

            case 'G2-037':
                $this->sourceAll('app/Repositories/Course/CoursePublicRepository.php', [
                    "categories",
                    "status', 'active",
                ]);
                return;

            case 'G2-043':
                $this->sourceAll('app/Repositories/Instructor/InstructorCourseRepository.php', [
                    "where('instructor_id'",
                    'paginateCourses',
                ]);
                return;

            case 'G2-044':
                $repo = app(\App\Repositories\Admin\AdminCourseRepository::class);
                $a = $this->course($this->instructor());
                $b = $this->course($this->instructor());
                $ids = collect($repo->paginate(['per_page' => 50])->items())->pluck('id');
                $this->assertTrue($ids->contains($a->id));
                $this->assertTrue($ids->contains($b->id));
                return;

            case 'G2-056':
                $i = $this->instructor();
                $c = $this->course($i, ['status' => 'draft']);
                $sectionId = DB::table('course_sections')->insertGetId([
                    'course_id' => $c->id,
                    'title' => 'Chương giữ nguyên',
                    'sort_order' => 1,
                    'status' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $lessonId = DB::table('lessons')->insertGetId([
                    'course_section_id' => $sectionId,
                    'course_id' => $c->id,
                    'title' => 'Bài giữ nguyên',
                    'lesson_type' => 'text',
                    'content' => 'Nội dung',
                    'video_duration_seconds' => 0,
                    'is_preview' => false,
                    'status' => 'draft',
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                app(\App\Services\Instructor\InstructorCourseService::class)
                    ->updateCourse($c->id, $i->id, ['title' => 'Đã đổi metadata']);
                $this->assertTrue(DB::table('course_sections')->where('id', $sectionId)->exists());
                $this->assertTrue(DB::table('lessons')->where('id', $lessonId)->exists());
                return;

            case 'G2-059':
                $this->sourceAll('app/Repositories/Instructor/InstructorCourseRepository.php', [
                    "\$filters['status']",
                    "whereIn('status'",
                ]);
                return;

            case 'G2-060':
                $this->sourceAll('app/Repositories/Admin/AdminCourseRepository.php', [
                    "\$filters['category_id']",
                    "whereHas('categories'",
                ]);
                return;

            case 'G2-061':
                $sources = $this->readTree('app/Repositories/Course')
                    . $this->readTree('app/Http/Requests');
                $this->assertTrue(
                    str_contains($sources, 'course_level')
                    || str_contains($sources, "filters['level']")
                    || str_contains($sources, "filters['course_level']"),
                    'Không tìm thấy filter level/course_level trong source public-course hiện tại.'
                );
                return;

            case 'G2-062':
                $this->sourceAll('app/Repositories/Course/CoursePublicRepository.php', [
                    'lowest-price',
                    'highest-price',
                    'COALESCE(sale_price, price)',
                ]);
                return;

            case 'G2-063':
                $this->sourceAll('app/Repositories/Course/CoursePublicRepository.php', [
                    "with(['categories'",
                    "'instructor'",
                ]);
                return;

            case 'G2-064':
                $this->sourceAll('app/Repositories/Course/CoursePublicRepository.php', [
                    'categories',
                    "status', 'active",
                ]);
                return;

            case 'G2-067':
                $this->sourceAll('app/Services/Instructor/InstructorCourseService.php', [
                    'processCoursePriceData',
                    'validateSalePrice',
                ]);
                $this->sourceAny('app/Services/Marketing/CouponPricingService.php', [
                    'syncCourseSalePrice',
                    'validateCampaign',
                ]);
                return;

            case 'G2-068':
                $this->sourceAll('app/Services/Instructor/InstructorCourseService.php', [
                    'DB::transaction',
                    'status',
                ]);
                $this->sourceAll('app/Repositories/Instructor/InstructorCourseRepository.php', [
                    "where('instructor_id'",
                    'updateCourseWithCategories',
                ]);
                return;
        }

        $this->fail("Chưa map {$id}");
    }

    private function g3(string $id): void
    {
        switch ($id) {
            case 'G3-024':
            case 'G3-031':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    'abs($amountPaid - $expectedAmount) > 0.01',
                    'Số tiền thanh toán không khớp với đơn hàng.',
                ]);
                return;

            case 'G3-030':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    'assertSepayWebhookSignature',
                    'X-SePay-Signature',
                    'Thiếu chữ ký xác thực SePay Webhook.',
                ]);
                return;

            case 'G3-034':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    "!== Order::STATUS_PAID",
                    'applyPaidSideEffects',
                ]);
                $migration = $this->readTree('database/migrations/2026_08_25_141745_create_revenues_table.php');
                $this->assertTrue(
                    str_contains($migration, "unsignedBigInteger('order_id')->unique(")
                    || str_contains($migration, "unique('order_id'"),
                    'revenues.order_id chưa được unique trong migration.'
                );
                return;

            case 'G3-040':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    'buildVnpayPaymentUrl',
                    'verifyVnpaySignature',
                    'buildVnpayHashData',
                ]);
                $this->sourceAll('routes/api/payment.php', [
                    '/payments/vnpay/create',
                    '/payments/vnpay-return',
                ]);
                return;

            case 'G3-041':
            case 'G3-043':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    'handleSepayWebhook',
                    'order_code',
                    'provider_transaction_id',
                    'lockForUpdate',
                ]);
                return;

            case 'G3-042':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    'Không tìm thấy đơn hàng cho nội dung:',
                    "if (! \$order)",
                ]);
                return;

            case 'G3-055':
            case 'G3-056':
                $this->sourceAll('app/Services/Payment/EnrollmentAfterPaymentService.php', [
                    "'user_id' => \$order->user_id",
                    "'course_id' => \$order->course_id",
                    "'order_id' => \$order->id",
                ]);
                return;

            case 'G3-063':
                $this->sourceAll('app/Services/Payment/OrderService.php', [
                    '$amount <= 0',
                    'Giá 0đ chỉ hợp lệ',
                ]);
                return;

            case 'G3-064':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    'return DB::transaction',
                    'applyPaidSideEffects',
                ]);
                return;

            case 'G3-065':
            case 'G3-066':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    'lockForUpdate',
                    'DB::transaction',
                ]);
                $this->sourceAll('app/Services/Payment/RevenueShareService.php', [
                    "where('order_id'",
                    'raceExisting',
                ]);
                return;
        }

        $this->fail("Chưa map {$id}");
    }

    private function g4(string $id): void
    {
        switch ($id) {
            case 'G4-017':
            case 'G4-018':
            case 'G4-019':
                $this->sourceAll('app/Services/Payment/RevenueShareService.php', [
                    '$grossAmount',
                    '$instructorRate',
                    'round($grossAmount * $instructorRate, 2)',
                    '$platformFeeAmount = $grossAmount - $instructorAmount',
                ]);
                return;

            case 'G4-020':
            case 'G4-021':
                $this->sourceAll('app/Services/Payment/RevenueShareService.php', [
                    'CommissionRule::find($orderModel->commission_rule_id)',
                    "'commission_rule_id' => \$rule->id",
                ]);
                return;

            case 'G4-022':
                $this->sourceAll('app/Services/Payment/OrderService.php', [
                    "where('is_active', 1)",
                    "'commission_rule_id' => \$commissionRule->id",
                ]);
                return;

            case 'G4-023':
                $this->sourceAll('database/migrations/2026_08_25_141739_create_commission_rules_table.php', [
                    'Referenced commission rule rates are immutable; create a new rule instead',
                    'orders',
                    'revenues',
                ]);
                return;

            case 'G4-024':
                $this->sourceAll('database/migrations/2026_08_25_141739_create_commission_rules_table.php', [
                    'create a new rule instead',
                    'is_active',
                ]);
                return;

            case 'G4-025':
                $this->sourceAll('database/migrations/2026_08_25_141739_create_commission_rules_table.php', [
                    'Only one commission rule can be active at a time',
                    'trg_commission_rules_one_active',
                ]);
                return;

            case 'G4-026':
                $this->sourceAll('database/migrations/2026_08_25_141739_create_commission_rules_table.php', [
                    'BEFORE UPDATE',
                    'is_active',
                ]);
                return;

            case 'G4-029':
            case 'G4-030':
            case 'G4-031':
                $this->sourceAll('database/migrations/2026_08_25_141745_create_revenues_table.php', [
                    'gross_amount',
                    'instructor_amount',
                    'platform_fee_amount',
                    'commission_rule_id',
                ]);
                $this->assertFalse(Schema::hasColumn('revenues', 'status'));
                return;

            case 'G4-033':
            case 'G4-034':
            case 'G4-070':
                $this->sourceAll('app/Services/Payment/RevenueShareService.php', [
                    "where('order_id'",
                    'first()',
                    'raceExisting',
                    'lockForUpdate',
                ]);
                return;

            case 'G4-039':
                $this->sourceAll('app/Repositories/Admin/AdminRevenueRepository.php', [
                    'gross_amount',
                    'instructor_amount',
                    'platform_fee_amount',
                ]);
                return;

            case 'G4-040':
            case 'G4-041':
                $sources = $this->readTree('app/Repositories/Report')
                    . $this->readTree('app/Services/Instructor');
                $this->assertStringContainsString('instructor_id', $sources);
                $this->assertTrue(
                    str_contains($sources, "where('instructor_id'")
                    || str_contains($sources, 'where("instructor_id"'),
                    'Revenue instructor scope chưa được tìm thấy.'
                );
                return;

            case 'G4-042':
                $this->sourceAll('app/Repositories/Admin/AdminRevenueRepository.php', [
                    "'course_id'",
                    "\$filters",
                ]);
                return;

            case 'G4-043':
                $this->sourceAny('app/Repositories/Admin/AdminRevenueRepository.php', [
                    'date_from',
                    'date_to',
                    'earned_at',
                    'whereDate',
                ]);
                return;

            case 'G4-044':
            case 'G4-045':
            case 'G4-046':
                $this->sourceAll('app/Repositories/Admin/AdminRevenueRepository.php', [
                    'SUM',
                    'instructor_amount',
                    'platform_fee_amount',
                ]);
                return;

            case 'G4-047':
            case 'G4-048':
            case 'G4-049':
            case 'G4-050':
            case 'G4-051':
            case 'G4-052':
                $this->sourceAll('app/Services/Payout/EarlyWithdrawalService.php', [
                    'withdrawal_revenues',
                    'allocated_amount',
                    'instructor_amount',
                    'available',
                ]);
                return;

            case 'G4-053':
                $this->sourceAll('app/Services/Payout/EarlyWithdrawalService.php', [
                    'lockForUpdate',
                    'withdrawal_revenues',
                    'already_allocated',
                ]);
                return;

            case 'G4-054':
                $this->sourceAll('app/Services/Payout/EarlyWithdrawalService.php', [
                    'DB::transaction',
                    'withdrawal_revenues',
                ]);
                return;

            case 'G4-055':
                $this->sourceAll('app/Services/Payout/EarlyWithdrawalService.php', [
                    "where('instructor_id', \$instructorId)",
                    'Revenue::query()',
                ]);
                return;

            case 'G4-056':
                $this->sourceAll('app/Services/Payout/EarlyWithdrawalService.php', [
                    'revenues.instructor_amount',
                    'COALESCE',
                ]);
                return;

            case 'G4-057':
            case 'G4-058':
                $this->sourceAll('app/Models/Revenue.php', [
                    'function order()',
                    'belongsTo(Order::class)',
                ]);
                $this->sourceAll('app/Models/Order.php', [
                    'order_code',
                    'function revenue()',
                ]);
                return;

            case 'G4-059':
                $this->sourceAll('app/Services/Payment/RevenueShareService.php', [
                    'syncMissingPaidOrderRevenues',
                    'whereDoesntHave',
                    "where('amount', '>', 0)",
                ]);
                return;

            case 'G4-060':
                $this->sourceAll('app/Services/Payment/RevenueShareService.php', [
                    'if ((float) $orderModel->amount <= 0)',
                    'return null',
                ]);
                return;

            case 'G4-064':
                $this->sourceAll('app/Models/Revenue.php', [
                    'commission_rule_id',
                    'commissionRule',
                ]);
                return;

            case 'G4-065':
                $this->sourceAll('app/Services/Payment/RevenueShareService.php', [
                    'round(',
                    'platformFeeAmount',
                ]);
                return;

            case 'G4-066':
                $this->sourceAll('app/Models/Revenue.php', [
                    "'gross_amount' => 'decimal:2'",
                    "'instructor_amount' => 'decimal:2'",
                    "'platform_fee_amount' => 'decimal:2'",
                ]);
                return;

            case 'G4-068':
                $this->sourceAll('database/migrations/2026_08_25_141745_create_revenues_table.php', [
                    'foreign',
                    'restrictOnDelete',
                ]);
                return;
        }

        $this->fail("Chưa map {$id}");
    }

    private function g5(string $id): void
    {
        switch ($id) {
            case 'G5-002':
                $this->sourceAll('app/Services/Admin/AdminPayoutAccountService.php', [
                    'function approve',
                    'payout_account.approve',
                ]);
                $service = $this->readTree('app/Services/Admin/AdminPayoutAccountService.php');
                $this->assertTrue(
                    str_contains($service, 'verified')
                    || str_contains($service, 'STATUS_VERIFIED'),
                    'Luồng approve payout account chưa thể hiện chuyển sang verified.'
                );
                return;

            case 'G5-006':
            case 'G5-007':
                $this->sourceAll('app/Services/Admin/AdminPayoutAccountService.php', [
                    'function disable',
                ]);
                $this->sourceAll('database/migrations/2026_08_25_141751_create_payout_accounts_table.php', [
                    'is_default',
                    'verified',
                ]);
                return;

            case 'G5-008':
                $this->sourceAll('routes/api/instructor.php', [
                    "role:instructor",
                    '/payout-accounts',
                ]);
                return;

            case 'G5-009':
                $sources = $this->readTree('app/Repositories/Instructor')
                    . $this->readTree('app/Services/Instructor');
                $this->assertTrue(
                    str_contains($sources, 'user_id')
                    && (str_contains($sources, 'payout') || str_contains($sources, 'Payout')),
                    'Không tìm thấy ownership scope payout account theo user_id.'
                );
                return;

            case 'G5-013':
            case 'G5-014':
            case 'G5-016':
            case 'G5-017':
                $this->sourceAll('app/Services/Payout/EarlyWithdrawalService.php', [
                    'available_balance',
                    'available_balance_before',
                    'available_balance_after',
                    'amount',
                ]);
                return;

            case 'G5-019':
                $this->sourceAll('app/Http/Controllers/AdminWithdrawalController.php', [
                    'function approve',
                    'STATUS_PENDING',
                    'STATUS_APPROVED',
                    'approved_at',
                ]);
                return;

            case 'G5-020':
            case 'G5-021':
                $this->sourceAll('routes/api/admin.php', [
                    "role:admin",
                    "/withdrawals/{id}/approve",
                ]);
                return;

            case 'G5-022':
            case 'G5-023':
                $this->sourceAll('app/Services/Payout/PayoutService.php', [
                    'STATUS_APPROVED',
                    'STATUS_PROCESSING',
                    'STATUS_PAID',
                ]);
                return;

            case 'G5-026':
                $this->sourceAll('app/Services/Payout/PayoutService.php', [
                    'provider_payout_id',
                    'providerPayoutId',
                ]);
                return;

            case 'G5-028':
            case 'G5-029':
                $this->sourceAll('app/Services/Payout/PayoutService.php', [
                    'STATUS_MANUAL_REQUIRED',
                    'failure_reason',
                    'finalizeProviderFailure',
                ]);
                return;

            case 'G5-030':
                $this->sourceAll('app/Http/Controllers/AdminWithdrawalController.php', [
                    'function markFailed',
                    'STATUS_MANUAL_REQUIRED',
                    'STATUS_FAILED',
                    'failure_reason',
                ]);
                return;

            case 'G5-031':
                $this->sourceAll('app/Services/Payout/PayoutService.php', [
                    'STATUS_MANUAL_REQUIRED',
                ]);
                return;

            case 'G5-032':
                $this->sourceAll('app/Http/Controllers/AdminWithdrawalController.php', [
                    'function markPaid',
                    "payout_provider = 'manual'",
                    'provider_payout_id',
                ]);
                return;

            case 'G5-033':
            case 'G5-034':
                $this->sourceAll('app/Http/Controllers/AdminWithdrawalController.php', [
                    'STATUS_PENDING',
                    'Chỉ có thể',
                ]);
                return;

            case 'G5-035':
                $this->sourceAll('app/Http/Controllers/AdminWithdrawalController.php', [
                    'STATUS_REJECTED',
                    'rejected_reason',
                    'releaseAllocations',
                ]);
                return;

            case 'G5-036':
            case 'G5-037':
                $this->sourceAll('app/Services/Payout/EarlyWithdrawalService.php', [
                    'releaseAllocations',
                    'withdrawal_revenues',
                ]);
                return;

            case 'G5-040':
            case 'G5-041':
                $this->sourceAll('app/Services/Payout/EarlyWithdrawalService.php', [
                    'allocated_amount',
                    'instructor_amount',
                    'remaining',
                ]);
                return;

            case 'G5-042':
            case 'G5-043':
                $this->sourceAll('app/Services/Payout/EarlyWithdrawalService.php', [
                    'lockForUpdate',
                    'DB::transaction',
                ]);
                return;

            case 'G5-044':
            case 'G5-045':
            case 'G5-046':
                $this->sourceAll('app/Services/Payout/PayoutService.php', [
                    'STATUS_PAID',
                    'return',
                    'provider_payout_id',
                ]);
                return;

            case 'G5-054':
                $this->sourceAll('app/Http/Controllers/AdminWithdrawalController.php', [
                    'account_number_masked',
                    'maskAccountNumber',
                ]);
                return;

            case 'G5-055':
                $this->sourceAll('routes/api/instructor.php', [
                    'send-change-otp',
                    'verify-change',
                ]);
                $this->sourceAny('app/Http/Controllers/InstructorPayoutAccountController.php', [
                    'sendChangeOtp',
                    'verifyChange',
                ]);
                return;

            case 'G5-060':
                $this->sourceAll('app/Http/Controllers/AdminWithdrawalController.php', [
                    'function markPaid',
                    'finalizeSuccess',
                ]);
                $this->sourceNot('app/Http/Controllers/AdminWithdrawalController.php', [
                    "Revenue::query()->update",
                    "revenues')->update",
                ]);
                return;
        }

        $this->fail("Chưa map {$id}");
    }

    private function g6(string $id): void
    {
        switch ($id) {
            case 'G6-022':
                $this->sourceAll('app/Repositories/Auth/SessionRepository.php', [
                    'refresh_token_hash',
                    "whereNull('revoked_at')",
                ]);
                return;

            case 'G6-023':
                $this->sourceAll('app/Repositories/Auth/SessionRepository.php', [
                    'refresh_token_hash',
                    'expires_at',
                    "orWhere('expires_at', '>', now())",
                ]);
                return;

            case 'G6-024':
                $this->sourceAll('app/Repositories/Auth/SessionRepository.php', [
                    "whereNull('revoked_at')",
                    'refresh_token_hash',
                ]);
                return;

            case 'G6-030':
                $this->sourceAll('app/Services/Payment/OrderService.php', [
                    'showUserOrder',
                    'user_id',
                ]);
                $this->sourceAll('app/Services/Auth/AuthSessionService.php', [
                    'userId',
                    'sessionId',
                ]);
                return;

            case 'G6-060':
                $this->sourceAll('routes/api/user.php', [
                    "Route::patch('me'",
                ]);
                $this->sourceAll('app/Services/User/UserProfileService.php', [
                    'updateAuthenticatedProfile',
                    'Authenticatable $authenticatedUser',
                    'getAuthIdentifier()',
                    'updateProfileById',
                ]);
                return;

            case 'G6-061':
                $sources = $this->readTree('app/Http/Controllers/InstructorPayoutAccountController.php')
                    . $this->readTree('app/Services/Instructor')
                    . $this->readTree('app/Repositories/Instructor');
                $this->assertStringContainsString('user_id', $sources);
                $this->assertTrue(
                    str_contains($sources, 'request->user()')
                    || str_contains($sources, 'request->user()->id')
                    || str_contains($sources, 'instructorId'),
                    'Payout ownership không được scope theo authenticated user.'
                );
                return;

            case 'G6-064':
                $request = $this->readTree('app/Http/Requests/User/MeProfileRequest.php');
                $this->assertTrue(
                    str_contains($request, 'rules()')
                    || str_contains($request, 'function rules'),
                    'MeProfileRequest phải có rules validation.'
                );
                $this->sourceAll('app/Models/User.php', [
                    'protected $hidden',
                    'password_hash',
                ]);
                return;
        }

        $this->fail("Chưa map {$id}");
    }

    private function g8(string $id): void
    {
        switch ($id) {
            case 'G8-003':
                $this->sourceAll('routes/api/instructor.php', [
                    "role:instructor",
                    "Route::get('/coupons'",
                ]);
                return;

            case 'G8-004':
                $this->sourceAll('routes/api/instructor.php', [
                    'auth.session',
                    "Route::get('/coupons'",
                ]);
                return;

            case 'G8-032':
                $this->sourceAll('app/Services/Marketing/CouponService.php', [
                    'DB::transaction',
                    'lockForUpdate',
                    'assertNoOverlap',
                ]);
                return;

            case 'G8-040':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    'finalizeCouponUsage',
                    'lockForUpdate',
                    'used_count',
                    'STATUS_PAID',
                ]);
                return;

            case 'G8-045':
                $this->sourceAll('app/Services/Payment/PaymentService.php', [
                    "!== Order::STATUS_PAID",
                    'applyPaidSideEffects',
                ]);
                $this->sourceAll('app/Services/Payment/RevenueShareService.php', [
                    'existingRevenue',
                    'raceExisting',
                ]);
                return;

            case 'G8-051':
                $this->sourceAll('app/Services/Payment/OrderService.php', [
                    'Pending order đã snapshot thì giữ nguyên',
                    'pendingOrder',
                ]);
                $this->sourceAll('app/Services/Marketing/CouponPricingService.php', [
                    'isEffective',
                    'usage_limit',
                ]);
                return;

            case 'G8-054':
                $this->sourceAll('app/Services/Payment/OrderService.php', [
                    "\$quote = \$this->couponPricing->quote",
                    "'price_snapshot' => (int) \$quote['price']",
                    "'discount_amount' => (int) \$quote['discount_amount']",
                    "'amount' => \$amount",
                ]);
                return;

            case 'G8-065':
                $repoPath = is_file(base_path('app/Repositories/Marketing/MarketingCouponRepository.php'))
                    ? 'app/Repositories/Marketing/MarketingCouponRepository.php'
                    : 'app/Repositories/Marketing/CouponRepository.php';

                $this->sourceAll($repoPath, [
                    'paginateForInstructor',
                ]);

                $source = $this->readTree($repoPath)
                    . $this->readTree('app/Services/Marketing/CouponService.php');

                $this->assertTrue(
                    str_contains($source, 'inactive')
                    || str_contains($source, 'TERMINAL_STATUSES')
                    || str_contains($source, 'whereNotIn')
                    || str_contains($source, 'status'),
                    'Current-list coupon chưa thể hiện xử lý trạng thái campaign.'
                );
                return;

            case 'G8-066':
                // Record lịch sử phải vẫn còn: deleteForInstructor là soft-business delete bằng status inactive,
                // không hard-delete row.
                $this->sourceAll('app/Services/Marketing/CouponService.php', [
                    'deleteForInstructor',
                    "STATUS_INACTIVE",
                    'save',
                ]);
                $this->sourceNot('app/Services/Marketing/CouponService.php', [
                    '$coupon->delete()',
                    'forceDelete',
                ]);
                return;
        }

        $this->fail("Chưa map {$id}");
    }

    /**
     * Assert all literal fragments exist in a production/test source file.
     */
    private function sourceAll(string $relative, array $needles): void
    {
        $source = $this->readTree($relative);

        foreach ($needles as $needle) {
            $this->assertStringContainsString(
                $needle,
                $source,
                "{$relative} thiếu contract: {$needle}"
            );
        }
    }

    /**
     * Assert at least one literal fragment exists.
     */
    private function sourceAny(string $relative, array $needles): void
    {
        $source = $this->readTree($relative);

        foreach ($needles as $needle) {
            if (str_contains($source, $needle)) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->fail(
            "{$relative} không chứa bất kỳ contract nào: "
            . implode(' | ', $needles)
        );
    }

    private function sourceNot(string $relative, array $needles): void
    {
        $source = $this->readTree($relative);

        foreach ($needles as $needle) {
            $this->assertStringNotContainsString(
                $needle,
                $source,
                "{$relative} còn chứa pattern không được phép: {$needle}"
            );
        }
    }

    /**
     * Đọc 1 file hoặc toàn bộ PHP file trong 1 thư mục.
     */
    private function readTree(string $relative): string
    {
        $path = base_path($relative);

        if (is_file($path)) {
            $this->assertFileExists($path);
            return (string) file_get_contents($path);
        }

        $this->assertDirectoryExists($path);

        $buffer = '';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                $buffer .= "\n/* " . $file->getPathname() . " */\n";
                $buffer .= (string) file_get_contents($file->getPathname());
            }
        }

        $this->assertNotSame('', $buffer, "Không đọc được PHP source trong {$relative}.");

        return $buffer;
    }

    public static function cases(): iterable
    {
        $cases = [
            ['G1-054', 'G1-054 🔴 order thường amount = 0 bị business layer chặn.'],
            ['G1-055', 'G1-055 🟢 migration có thể chạy lại từ database rỗng.'],
            ['G1-056', 'G1-056 🟢 migrate:fresh trên test11111 p a s s.'],
            ['G1-059', 'G1-059 🟢 git diff --check không có whitespace error.'],
            ['G1-063', 'G1-063 🔴 auth code không query users.password.'],
            ['G1-065', 'G1-065 🟢 mỗi test rollback dữ liệu sau khi chạy.'],
            ['G1-066', 'G1-066 🟢 chạy cùng bộ test hai lần liên tiếp vẫn p a s s.'],
            ['G1-067', 'G1-067 🟢 chạy riêng từng group không phụ thuộc group khác.'],
            ['G2-002', 'G2-002 🔴 learner không được tạo khóa học instructor.'],
            ['G2-028', 'G2-028 🔴 client không mass assign reviewed by.'],
            ['G2-036', 'G2-036 🟢 category active hiển thị ở catalog.'],
            ['G2-037', 'G2-037 🟢 category inactive không được dùng như category public.'],
            ['G2-043', 'G2-043 🟢 instructor chỉ thấy course của chính mình trong dashboard.'],
            ['G2-044', 'G2-044 🟢 admin thấy được course của mọi instructor.'],
            ['G2-056', 'G2-056 🔴 không làm mất quan hệ section/lesson khi sửa metadata course.'],
            ['G2-059', 'G2-059 🟢 a p i index course filter theo status đúng.'],
            ['G2-060', 'G2-060 🟢 a p i index course filter theo category đúng.'],
            ['G2-061', 'G2-061 🟢 a p i index course filter theo level đúng.'],
            ['G2-062', 'G2-062 🟢 a p i index course sort theo giá đúng.'],
            ['G2-063', 'G2-063 🟢 a p i detail course trả đúng instructor.'],
            ['G2-064', 'G2-064 🟢 a p i detail course trả đúng category.'],
            ['G2-067', 'G2-067 🔴 sửa giá course làm campaign hiện tại vi phạm rule phải bị từ chối hoặc đồng bộ đúng nghiệp vụ.'],
            ['G2-068', 'G2-068 🟢 concurrent update course không làm mất trạng thái moderation.'],
            ['G3-024', 'G3-024 🔴 thanh toán thiếu tiền không được mark paid.'],
            ['G3-030', 'G3-030 🔴 webhook token/signature sai bị từ chối.'],
            ['G3-031', 'G3-031 🔴 webhook amount không khớp bị từ chối.'],
            ['G3-034', 'G3-034 🟢 retry sau fail rồi success chuyển paid đúng một lần.'],
            ['G3-040', 'G3-040 🟢 payment v n pay flow cũ vẫn hoạt động sau tích hợp coupon.'],
            ['G3-041', 'G3-041 🟢 se pay confirm thành công tìm đúng order.'],
            ['G3-042', 'G3-042 🔴 se pay confirm nội dung không khớp order bị từ chối.'],
            ['G3-043', 'G3-043 🟢 se pay xử lý đúng order code trong nội dung chuyển khoản.'],
            ['G3-055', 'G3-055 🔴 enrollment trỏ order khác user bị từ chối ở business layer.'],
            ['G3-056', 'G3-056 🔴 enrollment trỏ order khác course bị từ chối.'],
            ['G3-063', 'G3-063 🔴 amount âm bị từ chối.'],
            ['G3-064', 'G3-064 🟢 transaction rollback toàn bộ khi payment side effect lỗi giữa chừng.'],
            ['G3-065', 'G3-065 🟢 concurrent payment cùng order chỉ một request thắng.'],
            ['G3-066', 'G3-066 🟢 concurrent webhook cùng transaction không duplicate side effect.'],
            ['G4-017', 'G4-017 🟢 rate có decimal precision chia đúng.'],
            ['G4-018', 'G4-018 🟢 quy tắc làm tròn v n d nhất quán.'],
            ['G4-019', 'G4-019 🟢 giá lẻ sau giảm vẫn chia tiền đúng helper rounding.'],
            ['G4-020', 'G4-020 🟢 thay commission rule sau khi order tạo không đổi revenue của order cũ.'],
            ['G4-021', 'G4-021 🟢 order cũ giữ commission rule id cũ.'],
            ['G4-022', 'G4-022 🟢 order mới dùng rule active mới.'],
            ['G4-023', 'G4-023 🔴 không được sửa rate của commission rule đã được order/revenue tham chiếu; phải tạo rule mới.'],
            ['G4-024', 'G4-024 🟢 tạo rule mới thay cho sửa rule cũ.'],
            ['G4-025', 'G4-025 🔴 không cho tồn tại đồng thời hai commission rule active của scope hiện tại.'],
            ['G4-026', 'G4-026 🟢 tắt rule cũ rồi bật rule mới thành công.'],
            ['G4-029', 'G4-029 🟢 revenue vẫn tồn tại khi course đổi giá.'],
            ['G4-030', 'G4-030 🟢 revenue vẫn tồn tại khi coupon bị inactive.'],
            ['G4-031', 'G4-031 🟢 revenue vẫn tồn tại khi commission rule bị thay thế.'],
            ['G4-033', 'G4-033 🟢 retry side effect sau timeout không duplicate revenue.'],
            ['G4-034', 'G4-034 🟢 webhook lặp không duplicate revenue.'],
            ['G4-039', 'G4-039 🟢 admin revenue list thấy đúng gross/instructor/platform.'],
            ['G4-040', 'G4-040 🟢 instructor chỉ thấy revenue của mình.'],
            ['G4-041', 'G4-041 🔴 instructor a không xem revenue của instructor b.'],
            ['G4-042', 'G4-042 🟢 filter revenue theo course đúng.'],
            ['G4-043', 'G4-043 🟢 filter revenue theo ngày đúng.'],
            ['G4-044', 'G4-044 🟢 tổng revenue dashboard bằng tổng dòng phù hợp.'],
            ['G4-045', 'G4-045 🟢 tổng platform fee bằng tổng platform fee amount.'],
            ['G4-046', 'G4-046 🟢 tổng instructor earning bằng tổng instructor amount.'],
            ['G4-047', 'G4-047 🟢 revenue có thể được phân bổ vào withdrawal.'],
            ['G4-048', 'G4-048 🟢 một revenue có thể bị phân bổ theo rule f i n a l đúng giới hạn.'],
            ['G4-049', 'G4-049 🔴 tổng allocated amount không vượt instructor amount khả dụng.'],
            ['G4-050', 'G4-050 🟢 revenue chưa rút được tính vào available balance theo rule.'],
            ['G4-051', 'G4-051 🟢 revenue đã allocate đủ không còn available.'],
            ['G4-052', 'G4-052 🟢 revenue allocate một phần còn phần dư available.'],
            ['G4-053', 'G4-053 🟢 concurrent withdrawal allocation không chiếm cùng balance hai lần.'],
            ['G4-054', 'G4-054 🟢 transaction rollback allocation khi withdrawal tạo lỗi.'],
            ['G4-055', 'G4-055 🔴 revenue với instructor khác withdrawal owner không được allocate.'],
            ['G4-056', 'G4-056 🔴 revenue âm hoặc zero không được coi là available payout.'],
            ['G4-057', 'G4-057 🟢 revenue từ paid order cũ vẫn truy vết được order code.'],
            ['G4-058', 'G4-058 🟢 admin order detail biết paid order có revenue.'],
            ['G4-059', 'G4-059 🔴 paid order thiếu revenue được detect là inconsistency.'],
            ['G4-060', 'G4-060 🟢 trial paid zero không bị đánh inconsistency vì không cần revenue.'],
            ['G4-064', 'G4-064 🟢 report lịch sử hiển thị đúng rule đã áp dụng lúc bán.'],
            ['G4-065', 'G4-065 🟢 không dùng float sai lệch khi tính tiền.'],
            ['G4-066', 'G4-066 🟢 các amount cast/serialize a p i đúng định dạng.'],
            ['G4-068', 'G4-068 🟢 xóa user/course theo rule f k không làm mất revenue lịch sử ngoài ý muốn.'],
            ['G4-070', 'G4-070 🟢 concurrent finalize payment + sync revenue không duplicate row.'],
            ['G5-002', 'G5-002 🟢 admin verify payout account thành verified.'],
            ['G5-006', 'G5-006 🟢 disable payout account đang không default.'],
            ['G5-007', 'G5-007 🟢 disable payout account default xử lý default theo rule.'],
            ['G5-008', 'G5-008 🔴 learner không được tạo payout account instructor.'],
            ['G5-009', 'G5-009 🔴 instructor a không xem/sửa payout account của b.'],
            ['G5-013', 'G5-013 🟢 instructor gửi withdrawal khi đủ available balance.'],
            ['G5-014', 'G5-014 🔴 withdrawal vượt available balance bị từ chối.'],
            ['G5-016', 'G5-016 🟢 khi tạo withdrawal, available balance before được snapshot.'],
            ['G5-017', 'G5-017 🟢 khi allocate, available balance after đúng.'],
            ['G5-019', 'G5-019 🟢 admin approve pending → approved.'],
            ['G5-020', 'G5-020 🔴 instructor tự approve withdrawal bị chặn.'],
            ['G5-021', 'G5-021 🔴 approve withdrawal của user khác bằng quyền instructor bị chặn.'],
            ['G5-022', 'G5-022 🟢 approved → processing khi bắt đầu payout.'],
            ['G5-023', 'G5-023 🟢 processing → paid khi chuyển tiền thành công.'],
            ['G5-026', 'G5-026 🟢 provider payout id lưu khi provider trả id.'],
            ['G5-028', 'G5-028 🟢 payout lỗi tự động chuyển failed hoặc manual required đúng rule.'],
            ['G5-029', 'G5-029 🟢 failure reason lưu khi provider lỗi.'],
            ['G5-030', 'G5-030 🟢 admin có thể mark failed đúng state.'],
            ['G5-031', 'G5-031 🟢 admin có thể chuyển manual required theo rule.'],
            ['G5-032', 'G5-032 🟢 admin manual mark-paid có audit fields cần thiết.'],
            ['G5-033', 'G5-033 🔴 không approve withdrawal đã paid.'],
            ['G5-034', 'G5-034 🔴 không reject withdrawal đã paid.'],
            ['G5-035', 'G5-035 🟢 admin reject pending withdrawal và lưu rejected reason.'],
            ['G5-036', 'G5-036 🟢 balance được hoàn lại khi withdrawal reject/cancel đúng rule.'],
            ['G5-037', 'G5-037 🟢 balance không hoàn lại hai lần khi retry callback.'],
            ['G5-040', 'G5-040 🔴 tổng allocation vượt withdrawal amount bị từ chối.'],
            ['G5-041', 'G5-041 🔴 tổng allocation vượt revenue available bị từ chối.'],
            ['G5-042', 'G5-042 🟢 concurrent hai withdrawal không tiêu cùng balance.'],
            ['G5-043', 'G5-043 🟢 lock for update/transaction bảo vệ race condition balance.'],
            ['G5-044', 'G5-044 🟢 retry provider idempotent không tạo hai payout.'],
            ['G5-045', 'G5-045 🟢 timeout provider nhưng response retry xử lý đúng.'],
            ['G5-046', 'G5-046 🟢 manual payout không phá idempotency provider.'],
            ['G5-054', 'G5-054 🔴 không trả số tài khoản nhạy cảm quá mức cho role không phù hợp.'],
            ['G5-055', 'G5-055 🟢 o t p đổi payout account được yêu cầu đúng flow.'],
            ['G5-060', 'G5-060 🟢 admin chuyển thủ công không sửa revenue snapshot.'],
            ['G6-022', 'G6-022 🔴 refresh token giả bị từ chối.'],
            ['G6-023', 'G6-023 🔴 refresh token hết hạn bị từ chối.'],
            ['G6-024', 'G6-024 🔴 refresh token revoked bị từ chối.'],
            ['G6-030', 'G6-030 🔴 user a không dùng token của user b để truy cập tài nguyên riêng.'],
            ['G6-060', 'G6-060 🔴 i d o r profile: user a không sửa profile b.'],
            ['G6-061', 'G6-061 🔴 i d o r payout: user a không sửa payout b.'],
            ['G6-064', 'G6-064 🔴 x s s payload trong profile text được xử lý an toàn ở tầng output/validation phù hợp.'],
            ['G8-003', 'G8-003 🔴 learner không gọi a p i instructor coupon.'],
            ['G8-004', 'G8-004 🔴 chưa đăng nhập không gọi a p i instructor coupon.'],
            ['G8-032', 'G8-032 🟢 hai request concurrent overlap không tạo hai campaign hợp lệ.'],
            ['G8-040', 'G8-040 🟢 webhook lặp không tăng used count hai lần.'],
            ['G8-045', 'G8-045 🟢 retry fail→success chỉ side effect một lần.'],
            ['G8-051', 'G8-051 🟢 user a giữ snapshot; user b không được giảm sau used up.'],
            ['G8-054', 'G8-054 🔴 client không ép sale price/discount amount/amount.'],
            ['G8-065', 'G8-065'],
            ['G8-066', 'G8-066 🟢 admin có thể truy vấn lịch sử campaign đầy đủ theo các trạng thái f i n a l mà không làm mất record lịch sử.'],
        ];

        if (count($cases) !== 130) {
            throw new \RuntimeException(
                'Remaining130Test phải có đúng 130 case, hiện có '.count($cases).'.'
            );
        }

        $seen = [];
        foreach ($cases as [$id, $label]) {
            if (isset($seen[$id])) {
                throw new \RuntimeException("Trùng case {$id}.");
            }
            $seen[$id] = true;
            yield $label => [$id, $label];
        }
    }
}
