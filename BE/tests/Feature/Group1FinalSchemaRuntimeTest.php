<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\Support\FinalTestData;
use Tests\TestCase;

final class Group1FinalSchemaRuntimeTest extends TestCase
{
    use DatabaseTransactions;
    use FinalTestData;

    #[TestDox('01. Bảng users tồn tại')]
    public function test_01_b_ng_users_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
    }
    #[TestDox('02. Bảng categories tồn tại')]
    public function test_02_b_ng_categories_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('categories'));
    }
    #[TestDox('03. Bảng courses tồn tại')]
    public function test_03_b_ng_courses_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('courses'));
    }
    #[TestDox('04. Bảng course_categories tồn tại')]
    public function test_04_b_ng_course_categories_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('course_categories'));
    }
    #[TestDox('05. Bảng commission_rules tồn tại')]
    public function test_05_b_ng_commission_rules_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('commission_rules'));
    }
    #[TestDox('06. Bảng coupons tồn tại')]
    public function test_06_b_ng_coupons_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('coupons'));
    }
    #[TestDox('07. Bảng orders tồn tại')]
    public function test_07_b_ng_orders_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('orders'));
    }
    #[TestDox('08. Bảng enrollments tồn tại')]
    public function test_08_b_ng_enrollments_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('enrollments'));
    }
    #[TestDox('09. Bảng revenues tồn tại')]
    public function test_09_b_ng_revenues_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('revenues'));
    }
    #[TestDox('10. Bảng sessions tồn tại')]
    public function test_10_b_ng_sessions_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('sessions'));
    }
    #[TestDox('11. Bảng user_otps tồn tại')]
    public function test_11_b_ng_user_otps_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('user_otps'));
    }
    #[TestDox('12. Bảng payout_accounts tồn tại')]
    public function test_12_b_ng_payout_accounts_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('payout_accounts'));
    }
    #[TestDox('13. Bảng withdraw_requests tồn tại')]
    public function test_13_b_ng_withdraw_requests_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('withdraw_requests'));
    }
    #[TestDox('14. Bảng withdrawal_revenues tồn tại')]
    public function test_14_b_ng_withdrawal_revenues_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('withdrawal_revenues'));
    }
    #[TestDox('15. Bảng wishlist tồn tại')]
    public function test_15_b_ng_wishlist_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('wishlist'));
    }
    #[TestDox('16. Bảng lesson_progress tồn tại')]
    public function test_16_b_ng_lesson_progress_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('lesson_progress'));
    }
    #[TestDox('17. Bảng video_progress tồn tại')]
    public function test_17_b_ng_video_progress_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('video_progress'));
    }
    #[TestDox('18. Bảng comments tồn tại')]
    public function test_18_b_ng_comments_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('comments'));
    }
    #[TestDox('19. Bảng course_reviews tồn tại')]
    public function test_19_b_ng_course_reviews_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('course_reviews'));
    }
    #[TestDox('20. Bảng notifications tồn tại')]
    public function test_20_b_ng_notifications_t_n_t_i(): void
    {
        $this->assertTrue(Schema::hasTable('notifications'));
    }
    #[TestDox('21. users không có deleted_at')]
    public function test_21_users_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('users','deleted_at'));
    }
    #[TestDox('22. categories không có deleted_at')]
    public function test_22_categories_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('categories','deleted_at'));
    }
    #[TestDox('23. courses không có deleted_at')]
    public function test_23_courses_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('courses','deleted_at'));
    }
    #[TestDox('24. orders không có deleted_at')]
    public function test_24_orders_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('orders','deleted_at'));
    }
    #[TestDox('25. revenues không có deleted_at')]
    public function test_25_revenues_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('revenues','deleted_at'));
    }
    #[TestDox('26. payout_accounts không có deleted_at')]
    public function test_26_payout_accounts_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('payout_accounts','deleted_at'));
    }
    #[TestDox('27. withdraw_requests không có deleted_at')]
    public function test_27_withdraw_requests_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('withdraw_requests','deleted_at'));
    }
    #[TestDox('28. enrollments không có deleted_at')]
    public function test_28_enrollments_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('enrollments','deleted_at'));
    }
    #[TestDox('29. lesson_progress không có deleted_at')]
    public function test_29_lesson_progress_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('lesson_progress','deleted_at'));
    }
    #[TestDox('30. video_progress không có deleted_at')]
    public function test_30_video_progress_kh_ng_c_deleted_at(): void
    {
        $this->assertFalse(Schema::hasColumn('video_progress','deleted_at'));
    }
    #[TestDox('31. Không có legacy users.password')]
    public function test_31_kh_ng_c_legacy_users_password(): void
    {
        $this->assertFalse(Schema::hasColumn('users','password'));
    }
    #[TestDox('32. Không có legacy courses.discount_percent')]
    public function test_32_kh_ng_c_legacy_courses_discount_percent(): void
    {
        $this->assertFalse(Schema::hasColumn('courses','discount_percent'));
    }
    #[TestDox('33. Không có legacy courses.level')]
    public function test_33_kh_ng_c_legacy_courses_level(): void
    {
        $this->assertFalse(Schema::hasColumn('courses','level'));
    }
    #[TestDox('34. Không có legacy revenues.status')]
    public function test_34_kh_ng_c_legacy_revenues_status(): void
    {
        $this->assertFalse(Schema::hasColumn('revenues','status'));
    }
    #[TestDox('35. Không có legacy lesson_progress.user_id')]
    public function test_35_kh_ng_c_legacy_lesson_progress_user_id(): void
    {
        $this->assertFalse(Schema::hasColumn('lesson_progress','user_id'));
    }
    #[TestDox('36. Không có legacy video_progress.user_id')]
    public function test_36_kh_ng_c_legacy_video_progress_user_id(): void
    {
        $this->assertFalse(Schema::hasColumn('video_progress','user_id'));
    }
    #[TestDox('37. Có users.password_hash')]
    public function test_37_c_users_password_hash(): void
    {
        $this->assertTrue(Schema::hasColumn('users','password_hash'));
    }
    #[TestDox('38. Có courses.course_level')]
    public function test_38_c_courses_course_level(): void
    {
        $this->assertTrue(Schema::hasColumn('courses','course_level'));
    }
    #[TestDox('39. Có courses.sale_price')]
    public function test_39_c_courses_sale_price(): void
    {
        $this->assertTrue(Schema::hasColumn('courses','sale_price'));
    }
    #[TestDox('40. Có orders.price_snapshot')]
    public function test_40_c_orders_price_snapshot(): void
    {
        $this->assertTrue(Schema::hasColumn('orders','price_snapshot'));
    }
    #[TestDox('41. Có orders.discount_amount')]
    public function test_41_c_orders_discount_amount(): void
    {
        $this->assertTrue(Schema::hasColumn('orders','discount_amount'));
    }
    #[TestDox('42. Có orders.commission_rule_id')]
    public function test_42_c_orders_commission_rule_id(): void
    {
        $this->assertTrue(Schema::hasColumn('orders','commission_rule_id'));
    }
    #[TestDox('43. Có revenues.commission_rule_id')]
    public function test_43_c_revenues_commission_rule_id(): void
    {
        $this->assertTrue(Schema::hasColumn('revenues','commission_rule_id'));
    }
    #[TestDox('44. Có sessions.refresh_token_hash')]
    public function test_44_c_sessions_refresh_token_hash(): void
    {
        $this->assertTrue(Schema::hasColumn('sessions','refresh_token_hash'));
    }
    #[TestDox('45. Có user_otps.code_hash')]
    public function test_45_c_user_otps_code_hash(): void
    {
        $this->assertTrue(Schema::hasColumn('user_otps','code_hash'));
    }
    #[TestDox('46. Có withdrawal_revenues.allocated_amount')]
    public function test_46_c_withdrawal_revenues_allocated_amount(): void
    {
        $this->assertTrue(Schema::hasColumn('withdrawal_revenues','allocated_amount'));
    }
    #[TestDox('47. Wishlist dùng composite PK')]
    public function test_47_wishlist_d_ng_composite_pk(): void
    {
        $i=collect(DB::select("SHOW INDEX FROM wishlist"))->where('Key_name','PRIMARY')->pluck('Column_name')->all();$this->assertSame(['user_id','course_id'],array_values($i));
    }
    #[TestDox('48. User fixture dùng password_hash')]
    public function test_48_user_fixture_d_ng_password_hash(): void
    {
        $u=$this->user();$this->assertDatabaseHas('users',['id'=>$u]);
    }
    #[TestDox('49. Course fixture chạy được')]
    public function test_49_course_fixture_ch_y_c(): void
    {
        $i=$this->user('instructor');$c=$this->course($i);$this->assertDatabaseHas('courses',['id'=>$c]);
    }
    #[TestDox('50. Rule fixture chạy được')]
    public function test_50_rule_fixture_ch_y_c(): void
    {
        $r=$this->rule();$this->assertDatabaseHas('commission_rules',['id'=>$r]);
    }

}
