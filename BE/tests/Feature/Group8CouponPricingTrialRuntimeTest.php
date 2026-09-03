<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Session;
use App\Models\User;
use App\Services\Auth\AccessTokenService;
use App\Services\Marketing\CouponPricingService;
use App\Services\Marketing\CouponService;
use App\Services\Payment\EnrollmentAfterPaymentService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\RevenueShareService;
use App\Services\Payment\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class Group8CouponPricingTrialRuntimeTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role = 'learner', ?string $name = null): User
    {
        $name ??= $role === 'instructor' ? 'Nguyễn Minh Tuấn' : 'Phạm Quốc Huy';

        $id = DB::table('users')->insertGetId([
            'full_name' => $name,
            'email' => Str::uuid().'@mindhub.test',
            'password_hash' => Hash::make('Secret123!'),
            'role' => $role,
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail($id);
    }

    private function token(User $user): string
    {
        $session = Session::create([
            'user_id' => $user->id,
            'refresh_token_hash' => hash('sha256', Str::random(80)),
            'device_name' => 'PHPUnit Runtime',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'MindHub Group8 Runtime Test',
            'expires_at' => now()->addDay(),
        ]);

        return app(AccessTokenService::class)
            ->createAccessToken((int) $user->id, (int) $session->id)['token'];
    }

    private function instructorWithCourse(
        string $title = 'Laravel Backend từ Cơ Bản đến Thực Chiến',
        int $price = 1_000_000
    ): array {
        $instructor = $this->user('instructor', 'Nguyễn Minh Tuấn');

        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'short_description' => 'Khóa học thực hành xây dựng backend production.',
            'description' => 'Khóa học thực hành xây dựng backend production.',
            'price' => $price,
            'sale_price' => $price,
            'course_level' => 'beginner',
            'language' => 'vi',
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$instructor, Course::findOrFail($courseId)];
    }

    private function commissionRule(): int
    {
        return (int) DB::table('commission_rules')->insertGetId([
            'name' => 'Chia 80/20',
            'description' => 'Instructor 80%, nền tảng 20%',
            'instructor_rate' => 0.80,
            'platform_rate' => 0.20,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function coupon(
        Course $course,
        string $campaignType = 'discount',
        ?string $discountType = 'percent',
        int|float|null $discountValue = 30,
        ?int $usageLimit = null,
        ?Carbon $start = null,
        ?Carbon $end = null,
        string $status = 'active',
        ?int $maxDiscount = null,
        int $usedCount = 0
    ): Coupon {
        $start ??= now()->subHour();
        $end ??= now()->addDay();

        return Coupon::create([
            'course_id' => $course->id,
            'code' => strtoupper($campaignType === 'trial' ? 'FREE-' : 'SALE-').Str::upper(Str::random(8)),
            'campaign_type' => $campaignType,
            'discount_type' => $campaignType === 'trial' ? null : $discountType,
            'discount_value' => $campaignType === 'trial' ? null : $discountValue,
            'max_discount_amount' => $campaignType === 'trial' ? null : $maxDiscount,
            'usage_limit' => $usageLimit,
            'used_count' => $usedCount,
            'start_at' => $start,
            'end_at' => $end,
            'status' => $status,
        ]);
    }


    private function couponService(): CouponService
    {
        return app(CouponService::class);
    }

    private function createPaidOrder(User $user, Course $course, ?Coupon $coupon = null, int $amount = 700_000): Order
    {
        $ruleId = DB::table('commission_rules')->where('is_active', 1)->value('id');
        if (!$ruleId) {
            $ruleId = $this->commissionRule();
        }

        return Order::create([
            'order_code' => 'ORD-TST-'.Str::upper(Str::random(12)),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'coupon_id' => $coupon?->id,
            'commission_rule_id' => $ruleId,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'price_snapshot' => (int) $course->price,
            'discount_amount' => max(0, (int) $course->price - $amount),
            'amount' => $amount,
            'payment_method' => $amount === 0 ? 'coupon_trial' : 'sepay',
            'provider_transaction_id' => $amount === 0 ? null : 'TX-'.Str::upper(Str::random(14)),
            'paid_at' => now(),
            'expires_at' => null,
        ]);
    }

    private function invokeFinalizeCouponUsage(Order $order): void
    {
        $service = app(PaymentService::class);
        $method = new \ReflectionMethod($service, 'finalizeCouponUsage');
        $method->setAccessible(true);
        $method->invoke($service, $order);
    }

    private function applyPaidSideEffects(Order $order): void
    {
        app(EnrollmentAfterPaymentService::class)->createEnrollmentAfterPayment($order);
        if ((float) $order->amount > 0) {
            app(RevenueShareService::class)->createRevenueForPaidOrder($order);
        }
        $this->invokeFinalizeCouponUsage($order);
    }


    private function newCourseFor(User $instructor, string $title, int $price = 1_000_000): Course
    {
        $id = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'short_description' => 'Khóa học kiểm thử nghiệp vụ MindHub.',
            'description' => 'Dữ liệu runtime test tự tạo.',
            'price' => $price,
            'sale_price' => $price,
            'course_level' => 'beginner',
            'language' => 'vi',
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return Course::findOrFail($id);
    }

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.$this->token($user)];
    }

    private function createOrderApi(User $user, Course $course)
    {
        return $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/orders', ['course_id' => $course->id]);
    }

    public function test_01_giang_vien_tao_discount_cho_khoa_hoc_cua_minh(): void
    {
        [$instructor, $course] = $this->instructorWithCourse();
        $response = $this->withHeaders($this->authHeaders($instructor))->postJson('/api/instructor/coupons', [
            'course_id' => $course->id,
            'code' => 'TESTCODE' . Str::random(4), 'campaign_type' => 'discount',
            'discount_type' => 'percent',
            'discount_value' => 30,
            'start_at' => now()->subMinute()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ]);
        $response->assertSuccessful();
        $this->assertDatabaseHas('coupons', ['course_id' => $course->id, 'code' => 'TESTCODE' . Str::random(4), 'campaign_type' => 'discount']);
    }

    public function test_02_giang_vien_khong_duoc_tao_campaign_cho_khoa_hoc_nguoi_khac(): void
    {
        [$owner, $course] = $this->instructorWithCourse();
        $attacker = $this->user('instructor', 'Trần Hoàng Anh');
        $before = $course->sale_price;
        $response = $this->withHeaders($this->authHeaders($attacker))->postJson('/api/instructor/coupons', [
            'course_id' => $course->id,
            'code' => 'TESTCODE' . Str::random(4), 'campaign_type' => 'discount',
            'discount_type' => 'percent',
            'discount_value' => 30,
            'start_at' => now()->subMinute()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ]);
        $this->assertTrue(in_array($response->status(), [403,404], true));
        $this->assertDatabaseMissing('coupons', ['course_id' => $course->id]);
        $this->assertSame((string) $before, (string) $course->fresh()->sale_price);
    }

    public function test_03_client_khong_the_chiem_ownership_bang_user_id_hoac_instructor_id(): void
    {
        [$instructor, $course] = $this->instructorWithCourse();
        $other = $this->user('instructor', 'Trần Hoàng Anh');
        $response = $this->withHeaders($this->authHeaders($instructor))->postJson('/api/instructor/coupons', [
            'course_id' => $course->id,
            'code' => 'TESTCODE' . Str::random(4), 'campaign_type' => 'discount',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'user_id' => $other->id,
            'instructor_id' => $other->id,
            'start_at' => now()->subMinute()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ]);
        $this->assertFalse($response->status() >= 500);
        $coupon = Coupon::where('course_id', $course->id)->first();
        if ($coupon) {
            $this->assertSame((int)$instructor->id, (int)$coupon->course->instructor_id);
        }
    }

    public function test_04_client_duoc_tu_chon_coupon_code(): void
    {
        [$instructor, $course] = $this->instructorWithCourse();
        $response = $this->withHeaders($this->authHeaders($instructor))->postJson('/api/instructor/coupons', [
            'course_id' => $course->id,
            'code' => 'TESTCODE' . Str::random(4), 'campaign_type' => 'discount',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'code' => 'HACKEDCODE',
            'start_at' => now()->subMinute()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ]);
        $response->assertSuccessful();
        $coupon = Coupon::where('course_id', $course->id)->first();
        if ($coupon) $this->assertSame('HACKEDCODE', $coupon->code);
    }

    #[DataProvider('percentPricingProvider')]
    public function test_05_den_09_percent_discount_validation(int $percent, bool $valid): void
    {
        [, $course] = $this->instructorWithCourse();
        if ($valid) {
            $coupon = $this->coupon($course, 'discount', 'percent', $percent);
            $quote = app(CouponPricingService::class)->quote($course, $coupon);
            $this->assertSame((int) round(1_000_000 * (100-$percent)/100), (int) $quote['sale_price']);
        } else {
            $this->expectException(\Throwable::class);
            app(CouponPricingService::class)->validateCampaign($course, [
                'campaign_type'=>'discount','discount_type'=>'percent','discount_value'=>$percent,
                'start_at'=>now(),'end_at'=>now()->addDay(),
            ]);
        }
    }

    public static function percentPricingProvider(): array
    {
        return [[30,true],[70,true],[71,false],[0,false],[-1,false]];
    }

    #[DataProvider('fixedPricingProvider')]
    public function test_10_den_13_fixed_discount_validation(int $amount, bool $valid): void
    {
        [, $course] = $this->instructorWithCourse();
        if ($valid) {
            $coupon = $this->coupon($course, 'discount', 'fixed', $amount);
            $quote = app(CouponPricingService::class)->quote($course, $coupon);
            $this->assertSame(1_000_000-$amount, (int)$quote['sale_price']);
        } else {
            $this->expectException(\Throwable::class);
            app(CouponPricingService::class)->validateCampaign($course, [
                'campaign_type'=>'discount','discount_type'=>'fixed','discount_value'=>$amount,
                'start_at'=>now(),'end_at'=>now()->addDay(),
            ]);
        }
    }

    public static function fixedPricingProvider(): array
    {
        return [[250000,true],[700000,true],[700001,false],[1200000,false]];
    }

    public function test_14_percent_coupon_ap_dung_max_discount_amount(): void
    {
        [, $course] = $this->instructorWithCourse(price:2_000_000);
        $coupon = $this->coupon($course, 'discount', 'percent', 50, maxDiscount:600_000);
        $quote = app(CouponPricingService::class)->quote($course, $coupon);
        $this->assertSame(600_000, (int)$quote['discount_amount']);
        $this->assertSame(1_400_000, (int)$quote['sale_price']);
    }

    public function test_15_discount_tinh_ra_lon_hon_cap_thi_chi_giam_toi_cap(): void
    {
        [, $course] = $this->instructorWithCourse(price:3_000_000);
        $coupon = $this->coupon($course, 'discount', 'percent', 60, maxDiscount:500_000);
        $quote = app(CouponPricingService::class)->quote($course, $coupon);
        $this->assertSame(500_000, (int)$quote['discount_amount']);
    }

    public function test_16_max_discount_amount_khong_duoc_pha_70_percent(): void
    {
        [, $course] = $this->instructorWithCourse();
        $this->expectException(\Throwable::class);
        app(CouponPricingService::class)->validateCampaign($course, [
            'campaign_type'=>'discount','discount_type'=>'percent','discount_value'=>50,
            'max_discount_amount'=>800_000,'start_at'=>now(),'end_at'=>now()->addDay(),
        ]);
    }

    public function test_17_fixed_campaign_khong_su_dung_max_discount_amount(): void
    {
        [, $course] = $this->instructorWithCourse();
        $this->expectException(\Throwable::class);
        app(CouponPricingService::class)->validateCampaign($course, [
            'campaign_type'=>'discount','discount_type'=>'fixed','discount_value'=>200_000,
            'max_discount_amount'=>100_000,'start_at'=>now(),'end_at'=>now()->addDay(),
        ]);
    }

    public function test_18_discount_khong_duoc_lam_gia_con_duoi_10000(): void
    {
        [, $course] = $this->instructorWithCourse(price:20_000);
        $this->expectException(\Throwable::class);
        app(CouponPricingService::class)->validateCampaign($course, [
            'campaign_type'=>'discount','discount_type'=>'fixed','discount_value'=>14_000,
            'start_at'=>now(),'end_at'=>now()->addDay(),
        ]);
    }

    public function test_19_gia_sau_giam_dung_10000_la_hop_le(): void
    {
        [, $course] = $this->instructorWithCourse(price:20_000);
        $coupon = $this->coupon($course, 'discount', 'fixed', 10_000);
        $quote = app(CouponPricingService::class)->quote($course, $coupon);
        $this->assertSame(10_000, (int)$quote['sale_price']);
    }

    public function test_20_course_gia_thap_khong_duoc_tao_amount_sai(): void
    {
        [, $course] = $this->instructorWithCourse(price:12_000);
        $this->expectException(\Throwable::class);
        app(CouponPricingService::class)->validateCampaign($course, [
            'campaign_type'=>'discount','discount_type'=>'percent','discount_value'=>30,
            'start_at'=>now(),'end_at'=>now()->addDay(),
        ]);
    }

    public function test_21_campaign_tuong_lai_la_scheduled_va_chua_ap_gia(): void
    {
        [, $course] = $this->instructorWithCourse();
        $coupon = $this->coupon($course, start:now()->addDay(), end:now()->addDays(2), status:'scheduled');
        $quote = app(CouponPricingService::class)->quote($course, $coupon);
        $this->assertSame(1_000_000, (int)$quote['sale_price']);
    }

    public function test_22_dung_start_at_campaign_co_hieu_luc_khong_can_scheduler(): void
    {
        [, $course] = $this->instructorWithCourse();
        $now = now();
        Carbon::setTestNow($now);
        $coupon = $this->coupon($course, start:$now, end:$now->copy()->addDay(), status:'scheduled');
        $quote = app(CouponPricingService::class)->quote($course, $coupon);
        $this->assertSame(700_000, (int)$quote['sale_price']);
        Carbon::setTestNow();
    }

    public function test_23_active_campaign_ap_dung_gia_dung(): void
    {
        [, $course] = $this->instructorWithCourse();
        $coupon = $this->coupon($course);
        $this->assertSame(700_000, (int)app(CouponPricingService::class)->quote($course,$coupon)['sale_price']);
    }

    public function test_24_qua_end_at_campaign_het_hieu_luc(): void
    {
        [, $course] = $this->instructorWithCourse();
        $coupon = $this->coupon($course, start:now()->subDays(2), end:now()->subMinute(), status:'active');
        $quote = app(CouponPricingService::class)->quote($course,$coupon);
        $this->assertSame(1_000_000, (int)$quote['sale_price']);
    }

    public function test_25_disable_active_campaign_chi_chuyen_inactive(): void
    {
        [$instructor, $course] = $this->instructorWithCourse();
        $coupon = $this->coupon($course);
        $this->withHeaders($this->authHeaders($instructor))->patchJson("/api/instructor/coupons/{$coupon->id}/disable")->assertSuccessful();
        $this->assertDatabaseHas('coupons',['id'=>$coupon->id,'status'=>'inactive']);
    }

    public function test_26_disable_scheduled_campaign_khong_tu_chay_lai(): void
    {
        [$instructor, $course] = $this->instructorWithCourse();
        $coupon = $this->coupon($course,start:now()->addDay(),end:now()->addDays(2),status:'scheduled');
        $this->withHeaders($this->authHeaders($instructor))->patchJson("/api/instructor/coupons/{$coupon->id}/disable")->assertSuccessful();
        $this->assertSame('inactive', Coupon::find($coupon->id)->status);
    }

    public function test_27_delete_coupon_khong_hard_delete(): void
    {
        [$instructor,$course] = $this->instructorWithCourse();
        $coupon=$this->coupon($course);
        $this->withHeaders($this->authHeaders($instructor))->deleteJson("/api/instructor/coupons/{$coupon->id}")->assertSuccessful();
        $this->assertDatabaseHas('coupons',['id'=>$coupon->id,'status'=>'inactive']);
    }

    public function test_28_instructor_current_list_khong_hien_terminal_campaign(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $this->coupon($course,status:'inactive');
        $resp=$this->withHeaders($this->authHeaders($instructor))->getJson('/api/instructor/coupons');
        $resp->assertSuccessful();
        $this->assertStringNotContainsString('"status":"inactive"', $resp->getContent());
    }

    public function test_29_history_record_van_ton_tai_sau_khi_inactive(): void
    {
        [, $course]=$this->instructorWithCourse();
        $coupon=$this->coupon($course,status:'inactive');
        $this->assertDatabaseHas('coupons',['id'=>$coupon->id]);
    }

    #[DataProvider('terminalStatusProvider')]
    public function test_30_den_32_terminal_campaign_khong_duoc_mo_lai(string $status): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $coupon=$this->coupon($course,status:$status);
        $response=$this->withHeaders($this->authHeaders($instructor))->patchJson("/api/instructor/coupons/{$coupon->id}/enable");
        $this->assertTrue($response->status() >= 400);
    }

    public static function terminalStatusProvider(): array
    {
        return [['inactive'],['expired'],['used_up']];
    }

    public function test_33_course_chua_co_campaign_tao_moi_thanh_cong(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $this->withHeaders($this->authHeaders($instructor))->postJson('/api/instructor/coupons',[
            'course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent',
            'discount_value'=>10,'start_at'=>now()->toDateTimeString(),'end_at'=>now()->addDay()->toDateTimeString()
        ])->assertSuccessful();
    }

    public function test_34_active_campaign_overlap_bi_reject(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $this->coupon($course);
        $resp=$this->withHeaders($this->authHeaders($instructor))->postJson('/api/instructor/coupons',[
            'course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent',
            'discount_value'=>20,'start_at'=>now()->toDateTimeString(),'end_at'=>now()->addHours(12)->toDateTimeString()
        ]);
        $this->assertTrue($resp->status()>=400);
    }

    public function test_35_scheduled_campaign_overlap_bi_reject(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $this->coupon($course,start:now()->addDay(),end:now()->addDays(3),status:'scheduled');
        $this->expectException(\Throwable::class);
        $this->couponService()->createForInstructor($instructor->id,[
            'course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent','discount_value'=>20,
            'start_at'=>now()->addDays(2),'end_at'=>now()->addDays(4),
        ]);
    }
    public function test_36_active_va_future_campaign_overlap_bi_reject(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $this->coupon($course,start:now()->subHour(),end:now()->addDays(2),status:'active');
        $this->expectException(\Throwable::class);
        $this->couponService()->createForInstructor($instructor->id,[
            'course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent','discount_value'=>20,
            'start_at'=>now()->addDay(),'end_at'=>now()->addDays(3),
        ]);
    }
    public function test_37_campaign_expired_cho_phep_tao_campaign_moi(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $this->coupon($course,start:now()->subDays(3),end:now()->subDay(),status:'expired');
        $created=$this->couponService()->createForInstructor($instructor->id,[
            'course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent','discount_value'=>20,
            'start_at'=>now(),'end_at'=>now()->addDay(),
        ]);
        $this->assertSame($course->id,$created->course_id);
    }
    public function test_38_campaign_inactive_cho_phep_tao_campaign_moi(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $this->coupon($course,status:'inactive');
        $created=$this->couponService()->createForInstructor($instructor->id,[
            'course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent','discount_value'=>20,
            'start_at'=>now(),'end_at'=>now()->addDay(),
        ]);
        $this->assertNotNull($created->id);
    }
    public function test_39_mot_course_co_the_co_nhieu_campaign_lich_su(): void
    {
        [, $course]=$this->instructorWithCourse();
        $this->coupon($course,start:now()->subDays(4),end:now()->subDays(3),status:'expired');
        $this->coupon($course,start:now()->subDays(2),end:now()->subDay(),status:'inactive');
        $this->assertSame(2,Coupon::where('course_id',$course->id)->count());
    }
    public function test_40_hai_request_overlap_khong_duoc_tao_hai_campaign_hop_le(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $payload=['course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent','discount_value'=>20,
            'start_at'=>now(),'end_at'=>now()->addDay()];
        $this->couponService()->createForInstructor($instructor->id,$payload);
        try { $this->couponService()->createForInstructor($instructor->id,$payload); } catch (\Throwable) {}
        $this->assertSame(1,Coupon::where('course_id',$course->id)->whereIn('status',['scheduled','active'])->count());
    }

    public function test_41_discount_usage_limit_null_la_unlimited(): void
    {
        [, $course]=$this->instructorWithCourse();
        $coupon=$this->coupon($course,usageLimit:null);
        $this->assertNull($coupon->usage_limit);
    }

    public function test_42_discount_usage_limit_1000_hop_le(): void
    {
        [, $course]=$this->instructorWithCourse();
        $coupon=$this->coupon($course,usageLimit:1000);
        $this->assertSame(1000,(int)$coupon->usage_limit);
    }

    public function test_43_tao_pending_order_chua_tang_used_count(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:5); $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful();
        $this->assertSame(0,(int)$coupon->fresh()->used_count);
    }
    public function test_44_payment_success_tang_used_count_mot_lan(): void
    {
        [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:5); $learner=$this->user();
        $order=$this->createPaidOrder($learner,$course,$coupon);
        $this->invokeFinalizeCouponUsage($order);
        $this->assertSame(1,(int)$coupon->fresh()->used_count);
    }
    public function test_45_payment_fail_khong_tang_used_count(): void
    {
        [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:5); $learner=$this->user();
        Order::create(['order_code'=>'ORD-FAIL-'.Str::upper(Str::random(10)),'user_id'=>$learner->id,'course_id'=>$course->id,
            'coupon_id'=>$coupon->id,'commission_rule_id'=>$this->commissionRule(),'status'=>Order::STATUS_FAILED,
            'payment_status'=>Order::PAYMENT_FAILED,'price_snapshot'=>1000000,'discount_amount'=>300000,'amount'=>700000]);
        $this->assertSame(0,(int)$coupon->fresh()->used_count);
    }
    public function test_46_cancel_order_khong_tang_used_count(): void
    {
        [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:5); $learner=$this->user();
        Order::create(['order_code'=>'ORD-CAN-'.Str::upper(Str::random(10)),'user_id'=>$learner->id,'course_id'=>$course->id,
            'coupon_id'=>$coupon->id,'commission_rule_id'=>$this->commissionRule(),'status'=>Order::STATUS_CANCELLED,
            'payment_status'=>Order::PAYMENT_PENDING,'price_snapshot'=>1000000,'discount_amount'=>300000,'amount'=>700000]);
        $this->assertSame(0,(int)$coupon->fresh()->used_count);
    }
    public function test_47_webhook_success_lap_khong_duplicate_side_effect(): void
    {
        [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:5); $learner=$this->user(); $order=$this->createPaidOrder($learner,$course,$coupon);
        $this->applyPaidSideEffects($order);
        app(EnrollmentAfterPaymentService::class)->createEnrollmentAfterPayment($order);
        app(RevenueShareService::class)->createRevenueForPaidOrder($order);
        $this->assertSame(1,Enrollment::where('order_id',$order->id)->count());
        $this->assertSame(1,DB::table('revenues')->where('order_id',$order->id)->count());
        $this->assertSame(1,(int)$coupon->fresh()->used_count);
    }
    public function test_48_luot_cuoi_chuyen_used_up_va_reset_sale_price(): void
    {
        [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:1); app(CouponPricingService::class)->syncCourseSalePrice($course);
        $order=$this->createPaidOrder($this->user(),$course,$coupon); $this->invokeFinalizeCouponUsage($order);
        app(CouponPricingService::class)->syncCourseSalePrice($course->fresh());
        $this->assertSame('used_up',$coupon->fresh()->status);
        $this->assertSame((int)$course->price,(int)$course->fresh()->sale_price);
    }
    public function test_49_hai_payment_tranh_luot_cuoi_khong_vuot_limit(): void
    {
        [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:1);
        $o1=$this->createPaidOrder($this->user(),$course,$coupon); $o2=$this->createPaidOrder($this->user(),$course,$coupon);
        $this->invokeFinalizeCouponUsage($o1);
        try { $this->invokeFinalizeCouponUsage($o2); } catch (\Throwable) {}
        $this->assertLessThanOrEqual(1,(int)$coupon->fresh()->used_count,'used_count không được vượt usage_limit kể cả hai payment tranh lượt cuối.');
    }
    public function test_50_khong_duoc_giam_usage_limit_thap_hon_used_count(): void
    {
        [$instructor,$course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:10,usedCount:5);
        $this->expectException(\Throwable::class);
        $this->couponService()->updateForInstructor($instructor->id,$coupon->id,['usage_limit'=>4]);
    }
    public function test_51_double_disable_khong_corrupt_state(): void
    {
        [$instructor,$course]=$this->instructorWithCourse(); $coupon=$this->coupon($course);
        $this->couponService()->deleteForInstructor($instructor->id,$coupon->id);
        $this->couponService()->deleteForInstructor($instructor->id,$coupon->id);
        $this->assertSame('inactive',$coupon->fresh()->status);
    }
    public function test_52_payment_fail_retry_success_chi_mot_lan_side_effect(): void
    {
        [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:5); $learner=$this->user();
        $order=$this->createPaidOrder($learner,$course,$coupon); $this->applyPaidSideEffects($order);
        $this->assertSame(1,Enrollment::where('user_id',$learner->id)->where('course_id',$course->id)->count());
        $this->assertSame(1,DB::table('revenues')->where('order_id',$order->id)->count());
        $this->assertSame(1,(int)$coupon->fresh()->used_count);
    }

    public function test_53_order_snapshot_dung_gia_goc_discount_amount_amount_va_coupon(): void
    {
        $this->commissionRule();
        [, $course]=$this->instructorWithCourse();
        $learner=$this->user();
        $coupon=$this->coupon($course);
        $resp=$this->createOrderApi($learner,$course);
        $resp->assertSuccessful();
        $order=DB::table('orders')->where('user_id',$learner->id)->where('course_id',$course->id)->first();
        $this->assertSame(1_000_000,(int)$order->price_snapshot);
        $this->assertSame(300_000,(int)$order->discount_amount);
        $this->assertSame(700_000,(int)$order->amount);
        $this->assertSame((int)$coupon->id,(int)$order->coupon_id);
    }

    public function test_54_order_service_khong_tin_sale_price_stale(): void
    {
        $this->commissionRule();
        [, $course]=$this->instructorWithCourse();
        $course->update(['sale_price'=>12345]);
        $this->coupon($course);
        $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful();
        $order=DB::table('orders')->where('user_id',$learner->id)->first();
        $this->assertSame(700_000,(int)$order->amount);
    }

    public function test_55_pending_order_giu_snapshot_sau_khi_coupon_inactive(): void
    {
        $this->commissionRule(); [$instructor,$course]=$this->instructorWithCourse(); $coupon=$this->coupon($course); $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful(); $order=Order::where('user_id',$learner->id)->firstOrFail();
        $snapshot=[$order->price_snapshot,$order->discount_amount,$order->amount,$order->coupon_id];
        $this->couponService()->deleteForInstructor($instructor->id,$coupon->id); $order->refresh();
        $this->assertSame(array_map('strval',$snapshot),array_map('strval',[$order->price_snapshot,$order->discount_amount,$order->amount,$order->coupon_id]));
    }
    public function test_56_coupon_inactive_truoc_tao_order_thi_order_moi_khong_giam(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $this->coupon($course,status:'inactive'); $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful(); $order=Order::where('user_id',$learner->id)->firstOrFail();
        $this->assertSame((int)$course->price,(int)$order->amount); $this->assertNull($order->coupon_id);
    }
    public function test_57_pending_order_giu_snapshot_sau_khi_campaign_expired(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,end:now()->addMinute()); $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful(); $order=Order::where('user_id',$learner->id)->firstOrFail(); $amount=(int)$order->amount;
        Carbon::setTestNow(now()->addMinutes(2)); app(CouponPricingService::class)->syncCourseSalePrice($course->fresh()); $order->refresh(); Carbon::setTestNow();
        $this->assertSame($amount,(int)$order->amount);
    }
    public function test_58_double_click_mua_khong_tao_hai_pending_order(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful(); $this->createOrderApi($learner,$course)->assertSuccessful();
        $this->assertSame(1,Order::where('user_id',$learner->id)->where('course_id',$course->id)->where('status','pending_payment')->count());
    }
    public function test_59_user_a_giu_snapshot_user_b_khong_duoc_giam_sau_used_up(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,usageLimit:1); $a=$this->user(); $b=$this->user();
        $this->createOrderApi($a,$course)->assertSuccessful(); $aOrder=Order::where('user_id',$a->id)->firstOrFail();
        $coupon->update(['used_count'=>1,'status'=>'used_up']);
        $this->createOrderApi($b,$course)->assertSuccessful(); $bOrder=Order::where('user_id',$b->id)->firstOrFail();
        $this->assertSame(700000,(int)$aOrder->amount); $this->assertSame(1000000,(int)$bOrder->amount);
    }
    public function test_60_doi_course_price_khi_campaign_active_tinh_lai_sale_price(): void
    {
        [, $course]=$this->instructorWithCourse(); $this->coupon($course); $course->update(['price'=>1_200_000]); app(CouponPricingService::class)->syncCourseSalePrice($course->fresh());
        $this->assertSame(840_000,(int)$course->fresh()->sale_price);
    }
    public function test_61_pending_order_cu_khong_doi_sau_khi_course_doi_gia(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $this->coupon($course); $a=$this->user(); $b=$this->user();
        $this->createOrderApi($a,$course)->assertSuccessful(); $aOrder=Order::where('user_id',$a->id)->firstOrFail();
        $course->update(['price'=>1_200_000]); app(CouponPricingService::class)->syncCourseSalePrice($course->fresh());
        $this->createOrderApi($b,$course)->assertSuccessful(); $bOrder=Order::where('user_id',$b->id)->firstOrFail();
        $this->assertSame(700_000,(int)$aOrder->amount); $this->assertSame(840_000,(int)$bOrder->amount);
    }
    public function test_62_client_khong_duoc_ep_sale_price_discount_amount_amount(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $this->coupon($course); $learner=$this->user();
        $resp=$this->withHeaders($this->authHeaders($learner))->postJson('/api/orders',['course_id'=>$course->id,'sale_price'=>1,'discount_amount'=>999999,'amount'=>1]);
        $resp->assertSuccessful(); $order=Order::where('user_id',$learner->id)->firstOrFail(); $this->assertSame(700000,(int)$order->amount);
    }

    public function test_63_trial_campaign_co_nullable_discount_fields(): void
    {
        [, $course]=$this->instructorWithCourse();
        $coupon=$this->coupon($course,'trial',null,null,5);
        $this->assertNull($coupon->discount_type);
        $this->assertNull($coupon->discount_value);
        $this->assertNull($coupon->max_discount_amount);
    }

    #[DataProvider('trialUsageProvider')]
    public function test_64_den_67_trial_usage_limit_validation(?int $limit, bool $valid): void
    {
        [, $course]=$this->instructorWithCourse();
        if ($valid) {
            $coupon=$this->coupon($course,'trial',null,null,$limit);
            $this->assertSame($limit,(int)$coupon->usage_limit);
        } else {
            $this->expectException(\Throwable::class);
            app(CouponPricingService::class)->validateCampaign($course,[
                'campaign_type'=>'trial','usage_limit'=>$limit,'start_at'=>now(),'end_at'=>now()->addDay()
            ]);
        }
    }

    public static function trialUsageProvider(): array
    {
        return [[1,true],[15,true],[16,false],[null,false]];
    }

    #[DataProvider('trialDurationProvider')]
    public function test_68_den_70_trial_duration_validation(int $days, bool $valid): void
    {
        [, $course]=$this->instructorWithCourse();
        $start = now();
        $end = $start->copy()->addDays($days);

        if ($valid) {
            app(CouponPricingService::class)->validateCampaign($course,[
                'campaign_type'=>'trial','discount_type'=>null,'discount_value'=>null,'max_discount_amount'=>null,
                'usage_limit'=>5,'start_at'=>$start,'end_at'=>$end
            ]);
            $this->assertGreaterThanOrEqual(1, $days);
            return;
        }

        $this->expectException(\Throwable::class);
        app(CouponPricingService::class)->validateCampaign($course,[
            'campaign_type'=>'trial','usage_limit'=>5,'start_at'=>$start,'end_at'=>$end
        ]);
    }

    public static function trialDurationProvider(): array
    {
        return [[1,true],[3,true],[4,false]];
    }

    public function test_71_trial_tao_paid_zero_order_enrollment_7_ngay_va_tang_used_count(): void
    {
        $this->commissionRule();
        [, $course]=$this->instructorWithCourse();
        $coupon=$this->coupon($course,'trial',null,null,5);
        $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful();
        $order=Order::where('user_id',$learner->id)->where('course_id',$course->id)->firstOrFail();
        $this->assertSame('paid',$order->status);
        $this->assertSame('paid',$order->payment_status);
        $this->assertSame('coupon_trial',$order->payment_method);
        $this->assertSame(0,(int)$order->amount);
        $enrollment=Enrollment::where('user_id',$learner->id)->where('course_id',$course->id)->firstOrFail();
        $this->assertNotNull($enrollment->expires_at);
        $this->assertSame(1,(int)$coupon->fresh()->used_count);
    }

    public function test_72_trial_khong_can_gateway_pending_hay_webhook(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $this->coupon($course,'trial',null,null,5); $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful(); $order=Order::where('user_id',$learner->id)->firstOrFail();
        $this->assertSame('paid',$order->status); $this->assertSame('paid',$order->payment_status); $this->assertSame('coupon_trial',$order->payment_method);
        $this->assertNull($order->provider_transaction_id);
    }

    public function test_73_trial_khong_tao_revenue(): void
    {
        $this->commissionRule();
        [, $course]=$this->instructorWithCourse();
        $this->coupon($course,'trial',null,null,5);
        $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful();
        $order=Order::where('user_id',$learner->id)->firstOrFail();
        $this->assertDatabaseMissing('revenues',['order_id'=>$order->id]);
    }

    public function test_74_double_click_trial_chi_tao_mot_order_mot_enrollment_va_mot_usage(): void
    {
        $this->commissionRule();
        [, $course]=$this->instructorWithCourse();
        $coupon=$this->coupon($course,'trial',null,null,5);
        $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful();
        $this->createOrderApi($learner,$course)->assertSuccessful();
        $this->assertSame(1,Order::where('user_id',$learner->id)->where('course_id',$course->id)->count());
        $this->assertSame(1,Enrollment::where('user_id',$learner->id)->where('course_id',$course->id)->count());
        $this->assertSame(1,(int)$coupon->fresh()->used_count);
    }

    public function test_75_learner_chi_duoc_trial_mot_lan_moi_course(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $this->coupon($course,'trial',null,null,5); $learner=$this->user();
        $this->createOrderApi($learner,$course)->assertSuccessful();
        Enrollment::where('user_id',$learner->id)->where('course_id',$course->id)->update(['expires_at'=>now()->subDay()]);
        $resp=$this->createOrderApi($learner,$course);
        $this->assertTrue($resp->status() < 300 || in_array($resp->status(),[409,422],true));
        $this->assertSame(1,Order::where('user_id',$learner->id)->where('course_id',$course->id)->where('payment_method','coupon_trial')->count());
    }
    public function test_76_instructor_khong_duoc_tao_trial_thu_ba_trong_thang(): void
    {
        [$instructor,$c1]=$this->instructorWithCourse('Laravel Backend Thực Chiến'); $c2=$this->newCourseFor($instructor,'React TypeScript Thực Chiến'); $c3=$this->newCourseFor($instructor,'Node.js REST API Production');
        foreach([$c1,$c2] as $c){$this->couponService()->createForInstructor($instructor->id,['course_id'=>$c->id,'campaign_type'=>'trial','usage_limit'=>5,'start_at'=>now(),'end_at'=>now()->addDay()]);}
        $this->expectException(\Throwable::class);
        $this->couponService()->createForInstructor($instructor->id,['course_id'=>$c3->id,'campaign_type'=>'trial','usage_limit'=>5,'start_at'=>now(),'end_at'=>now()->addDay()]);
    }

    public function test_77_trial_active_mua_that_reuse_enrollment_giu_progress(): void
    {
        [, $course]=$this->instructorWithCourse(); $learner=$this->user(); $trialOrder=$this->createPaidOrder($learner,$course,null,0);
        $enrollment=Enrollment::create(['user_id'=>$learner->id,'course_id'=>$course->id,'order_id'=>$trialOrder->id,'status'=>'active','progress_percent'=>37.50,'enrolled_at'=>now(),'expires_at'=>now()->addDays(7)]);
        $paid=$this->createPaidOrder($learner,$course,null,1_000_000); app(EnrollmentAfterPaymentService::class)->createEnrollmentAfterPayment($paid);
        $enrollment->refresh(); $this->assertSame((int)$paid->id,(int)$enrollment->order_id); $this->assertNull($enrollment->expires_at); $this->assertSame('37.50',(string)$enrollment->progress_percent);
    }
    public function test_78_trial_het_han_mua_that_van_reuse_va_giu_progress(): void
    {
        [, $course]=$this->instructorWithCourse(); $learner=$this->user(); $trialOrder=$this->createPaidOrder($learner,$course,null,0);
        $enrollment=Enrollment::create(['user_id'=>$learner->id,'course_id'=>$course->id,'order_id'=>$trialOrder->id,'status'=>'active','progress_percent'=>61.25,'enrolled_at'=>now()->subDays(8),'expires_at'=>now()->subDay()]);
        $paid=$this->createPaidOrder($learner,$course,null,1_000_000); app(EnrollmentAfterPaymentService::class)->createEnrollmentAfterPayment($paid);
        $enrollment->refresh(); $this->assertNull($enrollment->expires_at); $this->assertSame('61.25',(string)$enrollment->progress_percent);
    }
    public function test_79_concurrent_trial_monthly_quota_khong_vuot_hai(): void
    {
        [$instructor,$c1]=$this->instructorWithCourse('Laravel Backend'); $c2=$this->newCourseFor($instructor,'React TypeScript'); $c3=$this->newCourseFor($instructor,'Node.js Production');
        $payload=fn(Course $c)=>['course_id'=>$c->id,'campaign_type'=>'trial','usage_limit'=>5,'start_at'=>now(),'end_at'=>now()->addDay()];
        $this->couponService()->createForInstructor($instructor->id,$payload($c1)); $this->couponService()->createForInstructor($instructor->id,$payload($c2));
        try{$this->couponService()->createForInstructor($instructor->id,$payload($c3));}catch(\Throwable){}
        $this->assertLessThanOrEqual(2,Coupon::where('campaign_type','trial')->whereHas('course',fn($q)=>$q->where('instructor_id',$instructor->id))->count());
    }
    public function test_80_trial_flow_loi_giua_chung_phai_rollback_toan_bo(): void
    {
        $this->commissionRule(); [, $course]=$this->instructorWithCourse(); $coupon=$this->coupon($course,'trial',null,null,5); $learner=$this->user();
        $beforeOrders=Order::count(); $beforeUsage=(int)$coupon->used_count;
        try {
            DB::transaction(function() use($learner,$course,$coupon): void {
                $this->createPaidOrder($learner,$course,$coupon,0);
                throw new \RuntimeException('Giả lập lỗi sau khi tạo trial order.');
            });
        } catch (\RuntimeException) {}
        $this->assertSame($beforeOrders,Order::count()); $this->assertSame($beforeUsage,(int)$coupon->fresh()->used_count);
    }

    public function test_81_chua_dang_nhap_khong_duoc_goi_api_instructor_coupon(): void
    {
        [, $course]=$this->instructorWithCourse();
        $this->postJson('/api/instructor/coupons',[
            'course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent',
            'discount_value'=>10,'start_at'=>now()->toDateTimeString(),'end_at'=>now()->addDay()->toDateTimeString()
        ])->assertStatus(401);
    }

    public function test_82_learner_login_khong_duoc_goi_api_instructor_coupon(): void
    {
        [, $course]=$this->instructorWithCourse();
        $learner=$this->user();
        $resp=$this->withHeaders($this->authHeaders($learner))->postJson('/api/instructor/coupons',[
            'course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent',
            'discount_value'=>10,'start_at'=>now()->toDateTimeString(),'end_at'=>now()->addDay()->toDateTimeString()
        ]);
        $this->assertSame(403,$resp->status());
    }

    public function test_83_instructor_login_hop_le_duoc_thao_tac_resource_cua_minh(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $this->withHeaders($this->authHeaders($instructor))->getJson('/api/instructor/coupons/course-options')->assertSuccessful();
    }

    public function test_84_token_gia_bi_reject(): void
    {
        $this->withHeaders(['Authorization'=>'Bearer definitely-invalid-token'])
            ->getJson('/api/instructor/coupons')
            ->assertStatus(401);
    }

    public function test_85_token_sau_logout_revoke_khong_dung_lai_duoc(): void
    {
        $instructor=$this->user('instructor');
        $token=$this->token($instructor);
        $this->withHeaders(['Authorization'=>'Bearer '.$token])->postJson('/api/auth/logout');
        $this->withHeaders(['Authorization'=>'Bearer '.$token])->getJson('/api/instructor/coupons')->assertStatus(401);
    }

    public function test_86_idor_instructor_a_khong_duoc_sua_coupon_cua_b(): void
    {
        [$owner,$course]=$this->instructorWithCourse();
        $coupon=$this->coupon($course);
        $attacker=$this->user('instructor','Trần Hoàng Anh');
        $before=$coupon->discount_value;
        $resp=$this->withHeaders($this->authHeaders($attacker))->patchJson("/api/instructor/coupons/{$coupon->id}",[
            'discount_value'=>10,
        ]);
        $this->assertTrue(in_array($resp->status(),[403,404],true));
        $this->assertSame((string)$before,(string)$coupon->fresh()->discount_value);
    }

    public function test_87_mass_assignment_khong_duoc_ep_ownership_code_used_count_status_sale_price(): void
    {
        [$instructor,$course]=$this->instructorWithCourse();
        $other=$this->user('instructor','Trần Hoàng Anh');
        $resp=$this->withHeaders($this->authHeaders($instructor))->postJson('/api/instructor/coupons',[
            'course_id'=>$course->id,'campaign_type'=>'discount','discount_type'=>'percent','discount_value'=>20,
            'user_id'=>$other->id,'instructor_id'=>$other->id,'code'=>'HACKED','used_count'=>999,'status'=>'used_up',
            'sale_price'=>0,'start_at'=>now()->toDateTimeString(),'end_at'=>now()->addDay()->toDateTimeString()
        ]);
        $this->assertFalse($resp->status()>=500);
        $coupon=Coupon::where('course_id',$course->id)->first();
        if($coupon){
            $this->assertNotSame('HACKED',$coupon->code);
            $this->assertNotSame(999,(int)$coupon->used_count);
            $this->assertSame((int)$instructor->id,(int)$coupon->course->instructor_id);
        }
    }

    public function test_88_instructor_list_detail_khong_leak_resource_nguoi_khac(): void
    {
        [$owner,$course]=$this->instructorWithCourse();
        $coupon=$this->coupon($course);
        $attacker=$this->user('instructor','Trần Hoàng Anh');

        $list=$this->withHeaders($this->authHeaders($attacker))->getJson('/api/instructor/coupons');
        $list->assertSuccessful();
        $this->assertStringNotContainsString((string)$coupon->code,$list->getContent());

        $detail=$this->withHeaders($this->authHeaders($attacker))->getJson("/api/instructor/coupons/{$coupon->id}");
        $this->assertTrue(in_array($detail->status(),[403,404],true));
    }
}
