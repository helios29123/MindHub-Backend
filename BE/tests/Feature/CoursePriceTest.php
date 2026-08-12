<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CoursePriceTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = User::create([
            'full_name' => 'Instructor Price Tester',
            'email' => 'price_tester_' . uniqid() . '@example.com',
            'password_hash' => bcrypt('password'),
            'role' => User::ROLE_INSTRUCTOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->category = Category::query()->firstOrCreate(
            ['slug' => 'test-price-category'],
            ['name' => 'Test Price Category', 'status' => 'active']
        );
    }

    public function test_create_course_without_discount(): void
    {
        $response = $this->actingAs($this->instructor)->postJson('/api/instructor/courses', [
            'title' => 'Khóa học Test Không Giảm Giá',
            'price' => 499000,
            'has_discount' => false,
            'discount_percent' => null,
            'category_ids' => [$this->category->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.price', 499000)
            ->assertJsonPath('data.sale_price', 499000)
            ->assertJsonPath('data.discount_percent', null)
            ->assertJsonPath('data.has_discount', false);
    }

    public function test_create_course_with_40_percent_discount(): void
    {
        $response = $this->actingAs($this->instructor)->postJson('/api/instructor/courses', [
            'title' => 'Khóa học Test Giảm 40%',
            'price' => 499000,
            'has_discount' => true,
            'discount_percent' => 40,
            'category_ids' => [$this->category->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.price', 499000)
            ->assertJsonPath('data.sale_price', 299400)
            ->assertJsonPath('data.discount_percent', 40)
            ->assertJsonPath('data.has_discount', true);
    }

    public function test_create_course_with_1_percent_discount(): void
    {
        $response = $this->actingAs($this->instructor)->postJson('/api/instructor/courses', [
            'title' => 'Khóa học Test Giảm 1%',
            'price' => 100000,
            'has_discount' => true,
            'discount_percent' => 1,
            'category_ids' => [$this->category->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.price', 100000)
            ->assertJsonPath('data.sale_price', 99000)
            ->assertJsonPath('data.discount_percent', 1);
    }

    public function test_create_course_with_99_percent_discount(): void
    {
        $response = $this->actingAs($this->instructor)->postJson('/api/instructor/courses', [
            'title' => 'Khóa học Test Giảm 99%',
            'price' => 100000,
            'has_discount' => true,
            'discount_percent' => 99,
            'category_ids' => [$this->category->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.price', 100000)
            ->assertJsonPath('data.sale_price', 1000)
            ->assertJsonPath('data.discount_percent', 99);
    }

    public function test_create_course_with_invalid_0_percent_discount(): void
    {
        $response = $this->actingAs($this->instructor)->postJson('/api/instructor/courses', [
            'title' => 'Khóa học Lỗi 0%',
            'price' => 499000,
            'has_discount' => true,
            'discount_percent' => 0,
            'category_ids' => [$this->category->id],
        ]);

        $response->assertStatus(422);
    }

    public function test_create_course_with_invalid_100_percent_discount(): void
    {
        $response = $this->actingAs($this->instructor)->postJson('/api/instructor/courses', [
            'title' => 'Khóa học Lỗi 100%',
            'price' => 499000,
            'has_discount' => true,
            'discount_percent' => 100,
            'category_ids' => [$this->category->id],
        ]);

        $response->assertStatus(422);
    }

    public function test_toggle_off_discount_resets_sale_price_to_original(): void
    {
        $course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Khóa học Đã Giảm Giá',
            'slug' => 'test-course-' . uniqid(),
            'price' => 499000,
            'sale_price' => 299400,
            'discount_percent' => 40,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->instructor)->patchJson("/api/instructor/courses/{$course->id}/draft", [
            'price' => 499000,
            'has_discount' => false,
            'discount_percent' => null,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.price', 499000)
            ->assertJsonPath('data.sale_price', 499000)
            ->assertJsonPath('data.discount_percent', null)
            ->assertJsonPath('data.has_discount', false);
    }
}
