<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AdminCategoryApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $learner;
    private string $suffix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = str_replace('.', '-', uniqid('category-', true));
        $this->admin = $this->createUser('Category Admin', 'category-admin-' . $this->suffix . '@mindhub.test', 'admin');
        $this->learner = $this->createUser('Category Learner', 'category-learner-' . $this->suffix . '@mindhub.test', 'learner');
    }

    public function test_admin_can_list_categories_with_summary_and_course_count(): void
    {
        $categoryId = $this->createCategory(['name' => 'Laravel', 'slug' => 'laravel-' . $this->suffix]);

        $response = $this->actingAs($this->admin)->getJson('/api/admin/categories?per_page=10');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['summary', 'items'], 'meta'])
            ->assertJsonFragment(['id' => $categoryId, 'course_count' => 0]);
    }

    public function test_list_supports_search_status_sort_and_pagination(): void
    {
        $this->createCategory(['name' => 'Search Alpha', 'slug' => 'search-alpha-' . $this->suffix, 'status' => 'active']);
        $this->createCategory(['name' => 'Search Beta', 'slug' => 'search-beta-' . $this->suffix, 'status' => 'inactive']);

        $response = $this->actingAs($this->admin)->getJson(
            '/api/admin/categories?search=Search&status=active&sort_by=name_desc&page=1&per_page=1'
        );

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('data.items.0.status', 'active');
    }

    public function test_deleted_filter_returns_only_soft_deleted_categories(): void
    {
        $categoryId = $this->createCategory(['slug' => 'deleted-' . $this->suffix, 'deleted_at' => now()]);

        $response = $this->actingAs($this->admin)->getJson('/api/admin/categories?status=deleted');

        $response->assertOk()
            ->assertJsonFragment(['id' => $categoryId])
            ->assertJsonPath('data.items.0.deleted_at', fn ($value): bool => $value !== null);
    }

    public function test_admin_can_show_category_detail(): void
    {
        $categoryId = $this->createCategory();

        $this->actingAs($this->admin)
            ->getJson('/api/admin/categories/' . $categoryId)
            ->assertOk()
            ->assertJsonPath('data.id', $categoryId)
            ->assertJsonStructure(['data' => ['statistics', 'courses', 'children']]);
    }

    public function test_admin_can_create_root_and_child_category(): void
    {
        $rootId = $this->createCategory(['slug' => 'root-' . $this->suffix]);

        $response = $this->actingAs($this->admin)->postJson('/api/admin/categories', [
            'name' => 'Child category',
            'slug' => 'child-create-' . $this->suffix,
            'parent_id' => $rootId,
            'sort_order' => 'a',
            'status' => 'active',
        ]);

        $response->assertCreated()->assertJsonPath('data.parent_id', $rootId);
        $this->assertDatabaseHas('categories', ['slug' => 'child-create-' . $this->suffix, 'parent_id' => $rootId]);
    }

    public function test_create_rejects_invalid_payload_and_slug_of_soft_deleted_category(): void
    {
        $slug = 'reserved-' . $this->suffix;
        $this->createCategory(['slug' => $slug, 'deleted_at' => now()]);

        $this->actingAs($this->admin)->postJson('/api/admin/categories', [
            'name' => '',
            'slug' => $slug,
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'slug']);
    }

    public function test_create_rejects_three_level_tree(): void
    {
        $rootId = $this->createCategory(['slug' => 'root-level-' . $this->suffix]);
        $childId = $this->createCategory(['parent_id' => $rootId, 'slug' => 'child-level-' . $this->suffix]);

        $this->actingAs($this->admin)->postJson('/api/admin/categories', [
            'name' => 'Grandchild',
            'slug' => 'grandchild-' . $this->suffix,
            'parent_id' => $childId,
        ])->assertUnprocessable();
    }

    public function test_admin_can_update_category(): void
    {
        $categoryId = $this->createCategory();

        $this->actingAs($this->admin)->patchJson('/api/admin/categories/' . $categoryId, [
            'name' => 'Tên đã cập nhật',
            'status' => 'inactive',
        ])->assertOk()->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('categories', ['id' => $categoryId, 'name' => 'Tên đã cập nhật']);
    }

    public function test_update_rejects_self_parent_and_root_with_children_becoming_child(): void
    {
        $rootId = $this->createCategory(['slug' => 'root-update-' . $this->suffix]);
        $this->createCategory(['parent_id' => $rootId, 'slug' => 'child-update-' . $this->suffix]);
        $otherRootId = $this->createCategory(['slug' => 'other-root-' . $this->suffix]);

        $this->actingAs($this->admin)
            ->patchJson('/api/admin/categories/' . $rootId, ['parent_id' => $rootId])
            ->assertUnprocessable();

        $this->actingAs($this->admin)
            ->patchJson('/api/admin/categories/' . $rootId, ['parent_id' => $otherRootId])
            ->assertUnprocessable();
    }

    public function test_admin_can_soft_delete_empty_leaf_category(): void
    {
        $categoryId = $this->createCategory();

        $this->actingAs($this->admin)->deleteJson('/api/admin/categories/' . $categoryId)->assertOk();
        $this->assertSoftDeleted('categories', ['id' => $categoryId]);
    }

    public function test_delete_rejects_category_with_children(): void
    {
        $rootId = $this->createCategory(['slug' => 'delete-root-' . $this->suffix]);
        $this->createCategory(['parent_id' => $rootId, 'slug' => 'delete-child-' . $this->suffix]);

        $this->actingAs($this->admin)->deleteJson('/api/admin/categories/' . $rootId)->assertStatus(409);
    }

    public function test_delete_rejects_category_with_linked_course(): void
    {
        $categoryId = $this->createCategory(['slug' => 'course-category-' . $this->suffix]);
        $courseId = $this->createCourse();

        DB::table('course_categories')->insert([
            'course_id' => $courseId,
            'category_id' => $categoryId,
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)->deleteJson('/api/admin/categories/' . $categoryId)->assertStatus(409);
        $this->assertDatabaseHas('categories', ['id' => $categoryId, 'deleted_at' => null]);
    }

    public function test_admin_can_restore_soft_deleted_category(): void
    {
        $categoryId = $this->createCategory(['slug' => 'restore-' . $this->suffix, 'deleted_at' => now()]);

        $this->actingAs($this->admin)
            ->postJson('/api/admin/categories/' . $categoryId . '/restore')
            ->assertOk()
            ->assertJsonPath('data.id', $categoryId);

        $this->assertDatabaseHas('categories', ['id' => $categoryId, 'deleted_at' => null]);
    }

    public function test_restore_rejects_child_when_parent_is_deleted(): void
    {
        $rootId = $this->createCategory(['slug' => 'restore-parent-' . $this->suffix, 'deleted_at' => now()]);
        $childId = $this->createCategory([
            'parent_id' => $rootId,
            'slug' => 'restore-child-' . $this->suffix,
            'deleted_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->postJson('/api/admin/categories/' . $childId . '/restore')
            ->assertStatus(409);
    }

    public function test_admin_can_reorder_categories(): void
    {
        $rootId = $this->createCategory(['slug' => 'reorder-root-' . $this->suffix]);
        $childId = $this->createCategory(['slug' => 'reorder-child-' . $this->suffix]);

        $this->actingAs($this->admin)->putJson('/api/admin/categories/reorder', [
            'items' => [
                ['id' => $rootId, 'parent_id' => null, 'sort_order' => 'b'],
                ['id' => $childId, 'parent_id' => $rootId, 'sort_order' => 'a'],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('categories', ['id' => $childId, 'parent_id' => $rootId, 'sort_order' => 'a']);
    }

    public function test_reorder_rejects_duplicate_id_and_invalid_sort_order(): void
    {
        $categoryId = $this->createCategory();

        $this->actingAs($this->admin)->putJson('/api/admin/categories/reorder', [
            'items' => [
                ['id' => $categoryId, 'parent_id' => null, 'sort_order' => 0], // integer 0 is invalid type
                ['id' => $categoryId, 'parent_id' => null, 'sort_order' => 'b'],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors(['items.0.id', 'items.0.sort_order']);
    }

    public function test_reorder_rolls_back_all_items_when_tree_is_invalid(): void
    {
        $rootId = $this->createCategory(['slug' => 'rollback-root-' . $this->suffix, 'sort_order' => 'a']);
        $childId = $this->createCategory(['parent_id' => $rootId, 'slug' => 'rollback-child-' . $this->suffix, 'sort_order' => 'a']);

        $this->actingAs($this->admin)->putJson('/api/admin/categories/reorder', [
            'items' => [
                ['id' => $rootId, 'parent_id' => $childId, 'sort_order' => 'i'],
                ['id' => $childId, 'parent_id' => $rootId, 'sort_order' => 'h'],
            ],
        ])->assertUnprocessable();

        $this->assertDatabaseHas('categories', ['id' => $rootId, 'parent_id' => null, 'sort_order' => 'a']);
        $this->assertDatabaseHas('categories', ['id' => $childId, 'parent_id' => $rootId, 'sort_order' => 'a']);
    }

    public function test_unauthenticated_user_cannot_access_admin_categories(): void
    {
        $this->getJson('/api/admin/categories')->assertUnauthorized();
    }

    public function test_non_admin_user_cannot_access_admin_categories(): void
    {
        $this->actingAs($this->learner)->getJson('/api/admin/categories')->assertForbidden();
    }

    private function createUser(string $fullName, string $email, string $role): User
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

    private function createCategory(array $overrides = []): int
    {
        return DB::table('categories')->insertGetId(array_merge([
            'parent_id' => null,
            'name' => 'Category ' . $this->suffix,
            'slug' => 'category-' . uniqid(),
            'description' => null,
            'sort_order' => 'a',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ], $overrides));
    }

    private function createCourse(): int
    {
        $data = [
            'instructor_id' => $this->admin->id,
            'title' => 'Category linked course ' . $this->suffix,
            'slug' => 'category-linked-course-' . $this->suffix,
            'short_description' => null,
            'description' => null,
            'thumbnail_url' => null,
            'intro_video_url' => null,
            'price' => 0,
            'sale_price' => null,
            'level' => 'beginner',
            'language' => 'vi',
            'requirements' => null,
            'outcomes' => null,
            'status' => 'draft',
            'is_featured' => false,
            'total_duration_seconds' => 0,
            'published_at' => null,
            'admin_reject_reason' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];

        foreach (array_keys($data) as $column) {
            if (!Schema::hasColumn('courses', $column)) {
                unset($data[$column]);
            }
        }

        return DB::table('courses')->insertGetId($data);
    }
}