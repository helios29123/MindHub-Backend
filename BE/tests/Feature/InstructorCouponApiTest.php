<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
final class InstructorCouponApiTest extends TestCase
{
    use DatabaseTransactions;
    private User $instructor;
    private User $otherInstructor;
    private int $courseId;
    private int $secondCourseId;
    private int $otherCourseId;
    private int $deletedCourseId;
    private int $activeCouponId;
    private int $inactiveCouponId;
    private int $expiredCouponId;
    private int $usedUpCouponId;
    private int $secondCourseCouponId;
    private int $otherInstructorCouponId;
    private int $globalCouponId;
    private int $softDeletedCouponId;
    private int $usedCouponId;
    private int $couponSequence = 1;
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $suffix = str_replace('.', '-', uniqid('coupon_', true));
        $this->instructor = $this->createUser('Coupon Instructor', 'coupon-instructor-' . $suffix . '@mindhub.test', 'instructor');
        $this->otherInstructor = $this->createUser('Other Coupon Instructor', 'other-coupon-instructor-' . $suffix . '@mindhub.test', 'instructor');
        $this->courseId = $this->createCourse((int) $this->instructor->id, 'Laravel Coupon Course ' . $suffix, 'laravel-coupon-course-' . $suffix);
        $this->secondCourseId = $this->createCourse((int) $this->instructor->id, 'PHP Coupon Course ' . $suffix, 'php-coupon-course-' . $suffix);
        $this->otherCourseId = $this->createCourse((int) $this->otherInstructor->id, 'Other Coupon Course ' . $suffix, 'other-coupon-course-' . $suffix);
        $this->deletedCourseId = $this->createCourse((int) $this->instructor->id, 'Deleted Coupon Course ' . $suffix, 'deleted-coupon-course-' . $suffix, now());
        $this->activeCouponId = $this->createCoupon([
            'user_id' => $this->instructor->id,
            'course_id' => $this->courseId,
            'code' => 'ACTIVE' . $this->couponSequence,
            'name' => 'Mã đang hoạt động keyword',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'max_order_amount' => 50000,
            'usage_limit' => 10,
            'used_count' => 2,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(10),
            'status' => 'active',
        ]);
        $this->inactiveCouponId = $this->createCoupon([
            'user_id' => $this->instructor->id,
            'course_id' => $this->courseId,
            'code' => 'INACTIVE' . $this->couponSequence,
            'name' => 'Mã đã tắt',
            'discount_type' => 'fixed',
            'discount_value' => 100000,
            'max_order_amount' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(10),
            'status' => 'inactive',
        ]);
        $this->expiredCouponId = $this->createCoupon([
            'user_id' => $this->instructor->id,
            'course_id' => $this->courseId,
            'code' => 'EXPIRED' . $this->couponSequence,
            'name' => 'Mã hết hạn',
            'discount_type' => 'percent',
            'discount_value' => 30,
            'max_order_amount' => null,
            'usage_limit' => 20,
            'used_count' => 1,
            'start_at' => now()->subDays(10),
            'end_at' => now()->subDay(),
            'status' => 'active',
        ]);
        $this->usedUpCouponId = $this->createCoupon([
            'user_id' => $this->instructor->id,
            'course_id' => $this->courseId,
            'code' => 'USEDUP' . $this->couponSequence,
            'name' => 'Mã hết lượt',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'max_order_amount' => null,
            'usage_limit' => 5,
            'used_count' => 5,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(10),
            'status' => 'active',
        ]);
        $this->secondCourseCouponId = $this->createCoupon([
            'user_id' => $this->instructor->id,
            'course_id' => $this->secondCourseId,
            'code' => 'SECOND' . $this->couponSequence,
            'name' => 'Mã course thứ hai',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'max_order_amount' => null,
            'usage_limit' => 50,
            'used_count' => 0,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(10),
            'status' => 'active',
        ]);
        $this->usedCouponId = $this->createCoupon([
            'user_id' => $this->instructor->id,
            'course_id' => $this->courseId,
            'code' => 'USED' . $this->couponSequence,
            'name' => 'Mã đã có lượt dùng',
            'discount_type' => 'fixed',
            'discount_value' => 20000,
            'max_order_amount' => null,
            'usage_limit' => 100,
            'used_count' => 12,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(10),
            'status' => 'active',
        ]);
        $this->otherInstructorCouponId = $this->createCoupon([
            'user_id' => $this->otherInstructor->id,
            'course_id' => $this->otherCourseId,
            'code' => 'OTHER' . $this->couponSequence,
            'name' => 'Mã của instructor khác',
            'discount_type' => 'percent',
            'discount_value' => 15,
            'max_order_amount' => null,
            'usage_limit' => 10,
            'used_count' => 0,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(10),
            'status' => 'active',
        ]);
        $this->globalCouponId = $this->createCoupon([
            'user_id' => $this->instructor->id,
            'course_id' => null,
            'code' => 'GLOBAL' . $this->couponSequence,
            'name' => 'Mã global không thuộc màn instructor',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'max_order_amount' => null,
            'usage_limit' => 10,
            'used_count' => 0,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(10),
            'status' => 'active',
        ]);
        $this->softDeletedCouponId = $this->createCoupon([
            'user_id' => $this->instructor->id,
            'course_id' => $this->courseId,
            'code' => 'DELETED' . $this->couponSequence,
            'name' => 'Mã đã xóa mềm',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'max_order_amount' => null,
            'usage_limit' => 10,
            'used_count' => 0,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(10),
            'status' => 'active',
            'deleted_at' => now(),
        ]);
    }
    public function test_instructor_can_get_coupon_summary(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons/summary');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_coupons', 6)
            ->assertJsonPath('data.active_coupons', 3)
            ->assertJsonPath('data.inactive_coupons', 1)
            ->assertJsonPath('data.expired_coupons', 1)
            ->assertJsonPath('data.used_up_coupons', 1);
    }
    public function test_summary_can_filter_by_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons/summary?course_id=' . $this->courseId);
        $response->assertOk()
            ->assertJsonPath('data.total_coupons', 5)
            ->assertJsonPath('data.active_coupons', 2)
            ->assertJsonPath('data.inactive_coupons', 1)
            ->assertJsonPath('data.expired_coupons', 1)
            ->assertJsonPath('data.used_up_coupons', 1);
    }
    public function test_summary_rejects_other_instructor_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons/summary?course_id=' . $this->otherCourseId);
        $response->assertNotFound();
    }
    public function test_instructor_can_list_all_coupons(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons?per_page=20');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 6);
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($this->activeCouponId, $ids);
        $this->assertContains($this->inactiveCouponId, $ids);
        $this->assertContains($this->expiredCouponId, $ids);
        $this->assertContains($this->usedUpCouponId, $ids);
        $this->assertNotContains($this->otherInstructorCouponId, $ids);
        $this->assertNotContains($this->globalCouponId, $ids);
        $this->assertNotContains($this->softDeletedCouponId, $ids);
    }
    public function test_instructor_can_filter_active_coupons(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons?status=active&per_page=20');
        $response->assertOk()
            ->assertJsonPath('meta.total', 3);
        foreach ($response->json('data') as $item) {
            $this->assertSame('active', $item['effective_status']);
        }
    }
    public function test_instructor_can_filter_inactive_coupons(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons?status=inactive');
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $this->inactiveCouponId)
            ->assertJsonPath('data.0.effective_status', 'inactive');
    }
    public function test_instructor_can_filter_expired_coupons(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons?status=expired');
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $this->expiredCouponId)
            ->assertJsonPath('data.0.effective_status', 'expired');
    }
    public function test_instructor_can_filter_used_up_coupons(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons?status=used_up');
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $this->usedUpCouponId)
            ->assertJsonPath('data.0.effective_status', 'used_up');
    }
    public function test_instructor_can_search_coupon_by_code_or_name(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons?search=keyword');
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $this->activeCouponId);
    }
    public function test_instructor_can_filter_coupon_by_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons?course_id=' . $this->secondCourseId);
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $this->secondCourseCouponId);
    }
    public function test_index_rejects_invalid_status(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons?status=draft');
        $response->assertUnprocessable();
    }
    public function test_instructor_can_create_percent_coupon(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/coupons', [
                'course_id' => $this->courseId,
                'code' => 'newpercent',
                'name' => 'Mã percent mới',
                'description' => 'Mô tả',
                'discount_type' => 'percent',
                'discount_value' => 50,
                'max_order_amount' => 100000,
                'usage_limit' => 20,
                'start_at' => now()->subHour()->format('Y-m-d H:i:s'),
                'end_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'status' => 'active',
            ]);
        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'NEWPERCENT')
            ->assertJsonPath('data.used_count', 0)
            ->assertJsonPath('data.effective_status', 'active');
        $this->assertDatabaseHas('coupons', [
            'course_id' => $this->courseId,
            'user_id' => $this->instructor->id,
            'code' => 'NEWPERCENT',
            'used_count' => 0,
        ]);
    }
    public function test_instructor_can_create_fixed_coupon(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/coupons', [
                'course_id' => $this->courseId,
                'code' => 'FIXEDNEW',
                'name' => 'Mã fixed mới',
                'discount_type' => 'fixed',
                'discount_value' => 100000,
                'usage_limit' => null,
                'status' => 'inactive',
            ]);
        $response->assertCreated()
            ->assertJsonPath('data.code', 'FIXEDNEW')
            ->assertJsonPath('data.discount_type', 'fixed')
            ->assertJsonPath('data.status', 'inactive');
    }
    public function test_create_rejects_global_coupon_without_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/coupons', [
                'code' => 'NOCourse',
                'name' => 'Không có course',
                'discount_type' => 'fixed',
                'discount_value' => 100000,
            ]);
        $response->assertUnprocessable();
    }
    public function test_create_rejects_other_instructor_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/coupons', [
                'course_id' => $this->otherCourseId,
                'code' => 'OTHERCOURSE',
                'name' => 'Sai course',
                'discount_type' => 'fixed',
                'discount_value' => 100000,
            ]);
        $response->assertNotFound();
    }
    public function test_create_rejects_percent_greater_than_100(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/coupons', [
                'course_id' => $this->courseId,
                'code' => 'PERCENT200',
                'name' => 'Sai percent',
                'discount_type' => 'percent',
                'discount_value' => 200,
            ]);
        $response->assertUnprocessable();
    }
    public function test_create_rejects_forbidden_used_count_field(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/coupons', [
                'course_id' => $this->courseId,
                'code' => 'FORBIDDENUSED',
                'name' => 'Có used count',
                'discount_type' => 'fixed',
                'discount_value' => 100000,
                'used_count' => 99,
            ]);
        $response->assertUnprocessable();
    }
    public function test_create_rejects_expired_active_coupon(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/coupons', [
                'course_id' => $this->courseId,
                'code' => 'EXPIREDACTIVE',
                'name' => 'Đã hết hạn',
                'discount_type' => 'fixed',
                'discount_value' => 100000,
                'end_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'status' => 'active',
            ]);
        $response->assertStatus(409);
    }
    public function test_instructor_can_show_coupon_detail(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons/' . $this->activeCouponId);
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->activeCouponId)
            ->assertJsonPath('data.course.id', $this->courseId)
            ->assertJsonPath('data.usage.used_count', 2)
            ->assertJsonPath('data.usage.remaining_usage', 8)
            ->assertJsonPath('data.note', 'Doanh thu giảng viên được tính trên số tiền học viên thực trả sau giảm giá.');
    }
    public function test_show_rejects_other_instructor_coupon(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons/' . $this->otherInstructorCouponId);
        $response->assertNotFound();
    }
    public function test_show_rejects_global_coupon(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons/' . $this->globalCouponId);
        $response->assertNotFound();
    }
    public function test_show_rejects_soft_deleted_coupon(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons/' . $this->softDeletedCouponId);
        $response->assertNotFound();
    }
    public function test_instructor_can_update_coupon(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->activeCouponId, [
                'name' => 'Tên mã đã cập nhật',
                'discount_type' => 'percent',
                'discount_value' => 40,
                'usage_limit' => 30,
            ]);
        $response->assertOk()
            ->assertJsonPath('data.id', $this->activeCouponId)
            ->assertJsonPath('data.name', 'Tên mã đã cập nhật')
            ->assertJsonPath('data.discount_value', 40)
            ->assertJsonPath('data.usage_limit', 30);
        $this->assertDatabaseHas('coupons', [
            'id' => $this->activeCouponId,
            'name' => 'Tên mã đã cập nhật',
            'usage_limit' => 30,
        ]);
    }
    public function test_instructor_can_update_code_when_coupon_unused(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->inactiveCouponId, [
                'code' => 'UPDATEDCODE',
            ]);
        $response->assertOk()
            ->assertJsonPath('data.code', 'UPDATEDCODE');
    }
    public function test_update_rejects_code_change_when_coupon_used(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->usedCouponId, [
                'code' => 'CANNOTCHANGE',
            ]);
        $response->assertStatus(409);
    }
    public function test_update_rejects_usage_limit_less_than_used_count(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->usedCouponId, [
                'usage_limit' => 3,
            ]);
        $response->assertUnprocessable();
    }
    public function test_update_rejects_used_count_field(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->activeCouponId, [
                'used_count' => 100,
            ]);
        $response->assertUnprocessable();
    }
    public function test_update_rejects_expired_or_used_up_status_from_instructor(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->activeCouponId, [
                'status' => 'expired',
            ]);
        $response->assertUnprocessable();
    }
    public function test_update_rejects_other_instructor_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->activeCouponId, [
                'course_id' => $this->otherCourseId,
            ]);
        $response->assertNotFound();
    }
    public function test_instructor_can_turn_coupon_inactive(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->activeCouponId . '/status', [
                'status' => 'inactive',
            ]);
        $response->assertOk()
            ->assertJsonPath('data.id', $this->activeCouponId)
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.effective_status', 'inactive');
    }
    public function test_instructor_can_turn_valid_coupon_active(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->inactiveCouponId . '/status', [
                'status' => 'active',
            ]);
        $response->assertOk()
            ->assertJsonPath('data.id', $this->inactiveCouponId)
            ->assertJsonPath('data.status', 'active');
    }
    public function test_status_rejects_expired_coupon_turn_active(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->expiredCouponId . '/status', [
                'status' => 'active',
            ]);
        $response->assertStatus(409);
    }
    public function test_status_rejects_used_up_coupon_turn_active(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->usedUpCouponId . '/status', [
                'status' => 'active',
            ]);
        $response->assertStatus(409);
    }
    public function test_status_rejects_extra_fields(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/coupons/' . $this->activeCouponId . '/status', [
                'status' => 'inactive',
                'name' => 'Không được sửa name ở API status',
            ]);
        $response->assertUnprocessable();
    }
    public function test_instructor_can_soft_delete_coupon(): void
    {
        $response = $this->actingAs($this->instructor)
            ->deleteJson('/api/instructor/coupons/' . $this->activeCouponId);
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->activeCouponId)
            ->assertJsonPath('data.deleted', true);
        $this->assertNotNull(DB::table('coupons')->where('id', $this->activeCouponId)->value('deleted_at'));
    }
    public function test_delete_rejects_other_instructor_coupon(): void
    {
        $response = $this->actingAs($this->instructor)
            ->deleteJson('/api/instructor/coupons/' . $this->otherInstructorCouponId);
        $response->assertNotFound();
    }
    public function test_deleted_coupon_no_longer_appears_in_list(): void
    {
        $this->actingAs($this->instructor)
            ->deleteJson('/api/instructor/coupons/' . $this->activeCouponId)
            ->assertOk();
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons?per_page=20');
        $ids = array_column($response->json('data'), 'id');
        $this->assertNotContains($this->activeCouponId, $ids);
    }
    public function test_instructor_can_get_course_options(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons/course-options');
        $response->assertOk()
            ->assertJsonPath('success', true);
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($this->courseId, $ids);
        $this->assertContains($this->secondCourseId, $ids);
        $this->assertNotContains($this->otherCourseId, $ids);
        $this->assertNotContains($this->deletedCourseId, $ids);
    }
    public function test_course_options_support_search(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/coupons/course-options?search=PHP');
        $response->assertOk();
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($this->secondCourseId, $ids);
        $this->assertNotContains($this->courseId, $ids);
    }
    private function createUser(string $fullName, string $email, string $role): User
    {
        $id = DB::table('users')->insertGetId([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => null,
            'role' => $role,
            'status' => 'active',
            'locked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return User::find($id);
    }
    private function createCourse(int $instructorId, string $title, string $slug, mixed $deletedAt = null): int
    {
        return DB::table('courses')->insertGetId([
            'instructor_id' => $instructorId,
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'Coupon course',
            'description' => 'Coupon course',
            'price' => 500000,
            'sale_price' => null,
            'level' => 'beginner',
            'language' => 'vi',
            'status' => 'published',
            'is_featured' => false,
            'total_duration_seconds' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => $deletedAt,
        ]);
    }
    private function createCoupon(array $overrides): int
    {
        $this->couponSequence++;
        $now = now()->addSeconds($this->couponSequence);
        $data = array_merge([
            'user_id' => $this->instructor->id,
            'course_id' => $this->courseId,
            'code' => 'COUPON' . $this->couponSequence,
            'name' => 'Coupon test',
            'description' => null,
            'discount_type' => 'percent',
            'discount_value' => 10,
            'max_order_amount' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'start_at' => null,
            'end_at' => null,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ], $overrides);
        return DB::table('coupons')->insertGetId($data);
    }
}