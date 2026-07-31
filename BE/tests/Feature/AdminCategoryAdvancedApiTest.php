<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AdminCategoryAdvancedApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $learner;
    private string $suffix;

    protected function setUp(): void
    {
        parent::setUp();
        $this->suffix = str_replace('.', '-', uniqid('advanced-category-', true));
        $this->admin = $this->createUser('Advanced Category Admin', 'advanced-admin-' . $this->suffix . '@mindhub.test', 'admin');
        $this->learner = $this->createUser('Advanced Category Learner', 'advanced-learner-' . $this->suffix . '@mindhub.test', 'learner');
    }

    public function test_default_list_excludes_soft_deleted_categories(): void
    {
        $visible = $this->createCategory(['name' => 'Default Visible ' . $this->suffix]);
        $deleted = $this->createCategory(['name' => 'Default Deleted ' . $this->suffix, 'deleted_at' => now()]);
        $items = $this->listIds('?search=' . urlencode($this->suffix));
        $this->assertContains($visible, $items);
        $this->assertNotContains($deleted, $items);
    }

    public function test_all_with_deleted_returns_normal_and_deleted_rows(): void
    {
        $normal = $this->createCategory(['name' => 'With Trashed Normal ' . $this->suffix]);
        $deleted = $this->createCategory(['name' => 'With Trashed Deleted ' . $this->suffix, 'deleted_at' => now()]);
        $ids = $this->listIds('?status=all_with_deleted&search=' . urlencode($this->suffix));
        $this->assertContains($normal, $ids);
        $this->assertContains($deleted, $ids);
    }

    public function test_status_all_excludes_deleted_but_keeps_active_and_inactive(): void
    {
        $active = $this->createCategory(['name' => 'All Active ' . $this->suffix, 'status' => 'active']);
        $inactive = $this->createCategory(['name' => 'All Inactive ' . $this->suffix, 'status' => 'inactive']);
        $deleted = $this->createCategory(['name' => 'All Deleted ' . $this->suffix, 'deleted_at' => now()]);
        $ids = $this->listIds('?status=all&search=' . urlencode($this->suffix));
        $this->assertContains($active, $ids);
        $this->assertContains($inactive, $ids);
        $this->assertNotContains($deleted, $ids);
    }

    public function test_active_filter_returns_only_active_rows(): void
    {
        $active = $this->createCategory(['name' => 'Status Active ' . $this->suffix, 'status' => 'active']);
        $inactive = $this->createCategory(['name' => 'Status Inactive ' . $this->suffix, 'status' => 'inactive']);
        $ids = $this->listIds('?status=active&search=' . urlencode($this->suffix));
        $this->assertContains($active, $ids);
        $this->assertNotContains($inactive, $ids);
    }

    public function test_inactive_filter_returns_only_inactive_rows(): void
    {
        $active = $this->createCategory(['name' => 'Inactive Group Active ' . $this->suffix, 'status' => 'active']);
        $inactive = $this->createCategory(['name' => 'Inactive Group Inactive ' . $this->suffix, 'status' => 'inactive']);
        $ids = $this->listIds('?status=inactive&search=' . urlencode($this->suffix));
        $this->assertContains($inactive, $ids);
        $this->assertNotContains($active, $ids);
    }

    public function test_type_root_returns_only_root_categories(): void
    {
        $root = $this->createCategory(['name' => 'Type Root ' . $this->suffix]);
        $child = $this->createCategory(['name' => 'Type Child ' . $this->suffix, 'parent_id' => $root]);
        $ids = $this->listIds('?type=root&search=' . urlencode($this->suffix));
        $this->assertContains($root, $ids);
        $this->assertNotContains($child, $ids);
    }

    public function test_type_child_returns_only_child_categories(): void
    {
        $root = $this->createCategory(['name' => 'Only Root ' . $this->suffix]);
        $child = $this->createCategory(['name' => 'Only Child ' . $this->suffix, 'parent_id' => $root]);
        $ids = $this->listIds('?type=child&search=' . urlencode($this->suffix));
        $this->assertContains($child, $ids);
        $this->assertNotContains($root, $ids);
    }

    public function test_parent_id_filter_does_not_mix_children_from_other_roots(): void
    {
        $rootA = $this->createCategory();
        $rootB = $this->createCategory();
        $childA = $this->createCategory(['parent_id' => $rootA]);
        $childB = $this->createCategory(['parent_id' => $rootB]);
        $ids = $this->listIds('?parent_id=' . $rootA);
        $this->assertContains($childA, $ids);
        $this->assertNotContains($childB, $ids);
    }

    public function test_empty_filter_returns_category_without_courses(): void
    {
        $empty = $this->createCategory(['name' => 'Empty Course ' . $this->suffix]);
        $ids = $this->listIds('?empty=true&search=' . urlencode($this->suffix));
        $this->assertContains($empty, $ids);
    }

    public function test_search_matches_slug_not_only_name(): void
    {
        $id = $this->createCategory(['name' => 'Unrelated Name', 'slug' => 'needle-' . $this->suffix]);
        $ids = $this->listIds('?search=' . urlencode('needle-' . $this->suffix));
        $this->assertSame([$id], $ids);
    }

    public function test_search_conditions_remain_grouped_with_status_filter(): void
    {
        $active = $this->createCategory(['name' => 'Grouped Search ' . $this->suffix, 'status' => 'active']);
        $inactive = $this->createCategory(['name' => 'Grouped Search ' . $this->suffix, 'status' => 'inactive']);
        $ids = $this->listIds('?search=' . urlencode('Grouped Search ' . $this->suffix) . '&status=active');
        $this->assertSame([$active], $ids);
        $this->assertNotContains($inactive, $ids);
    }

    public function test_name_ascending_sort_is_stable(): void
    {
        $beta = $this->createCategory(['name' => 'Sort Beta ' . $this->suffix]);
        $alpha = $this->createCategory(['name' => 'Sort Alpha ' . $this->suffix]);
        $ids = $this->listIds('?search=' . urlencode('Sort ') . '&sort_by=name_asc&per_page=100');
        $filtered = array_values(array_intersect($ids, [$alpha, $beta]));
        $this->assertSame([$alpha, $beta], $filtered);
    }

    public function test_name_descending_sort_is_stable(): void
    {
        $alpha = $this->createCategory(['name' => 'Descending Alpha ' . $this->suffix]);
        $beta = $this->createCategory(['name' => 'Descending Beta ' . $this->suffix]);
        $ids = $this->listIds('?search=' . urlencode('Descending ') . '&sort_by=name_desc&per_page=100');
        $this->assertSame([$beta, $alpha], array_values(array_intersect($ids, [$alpha, $beta])));
    }

    public function test_sort_order_descending_uses_requested_direction(): void
    {
        $low = $this->createCategory(['name' => 'Order Group ' . $this->suffix, 'sort_order' => 2]);
        $high = $this->createCategory(['name' => 'Order Group ' . $this->suffix, 'sort_order' => 99]);
        $ids = $this->listIds('?search=' . urlencode('Order Group ' . $this->suffix) . '&sort_by=sort_order_desc');
        $this->assertSame([$high, $low], $ids);
    }

    public function test_explicit_id_sort_with_asc_direction_is_whitelisted(): void
    {
        $first = $this->createCategory(['name' => 'ID Group ' . $this->suffix]);
        $second = $this->createCategory(['name' => 'ID Group ' . $this->suffix]);
        $ids = $this->listIds('?search=' . urlencode('ID Group ' . $this->suffix) . '&sort_by=id&sort_direction=asc');
        $this->assertSame([$first, $second], $ids);
    }

    public function test_pagination_total_is_not_count_of_current_page(): void
    {
        foreach (range(1, 3) as $index) {
            $this->createCategory(['name' => 'Page Total ' . $this->suffix . ' ' . $index]);
        }
        $response = $this->actingAs($this->admin)->getJson('/api/admin/categories?search=' . urlencode('Page Total ' . $this->suffix) . '&per_page=1');
        $response->assertOk()->assertJsonPath('meta.per_page', 1)->assertJsonPath('meta.total', 3);
        $this->assertCount(1, $response->json('data.items'));
    }

    public function test_second_page_returns_different_row(): void
    {
        foreach (range(1, 3) as $index) {
            $this->createCategory(['name' => 'Second Page ' . $this->suffix . ' ' . $index, 'sort_order' => $index]);
        }
        $first = $this->listIds('?search=' . urlencode('Second Page ' . $this->suffix) . '&sort_by=sort_order_asc&per_page=1&page=1');
        $second = $this->listIds('?search=' . urlencode('Second Page ' . $this->suffix) . '&sort_by=sort_order_asc&per_page=1&page=2');
        $this->assertCount(1, $first);
        $this->assertCount(1, $second);
        $this->assertNotSame($first[0], $second[0]);
    }

    public function test_per_page_upper_boundary_100_is_accepted(): void
    {
        $this->actingAs($this->admin)->getJson('/api/admin/categories?per_page=100')
            ->assertOk()->assertJsonPath('meta.per_page', 100);
    }

    public function test_per_page_above_100_is_rejected(): void
    {
        $this->actingAs($this->admin)->getJson('/api/admin/categories?per_page=101')
            ->assertUnprocessable()->assertJsonValidationErrors(['per_page']);
    }

    public function test_invalid_list_query_values_are_rejected(): void
    {
        $this->actingAs($this->admin)->getJson('/api/admin/categories?page=0&status=archived&sort_by=password&sort_direction=sideways')
            ->assertUnprocessable()->assertJsonValidationErrors(['page', 'status', 'sort_by', 'sort_direction']);
    }

    public function test_show_nonexistent_category_returns_404(): void
    {
        $this->actingAs($this->admin)->getJson('/api/admin/categories/999999999')->assertNotFound();
    }

    public function test_create_applies_default_status_and_next_sort_order(): void
    {
        $max = (int) DB::table('categories')->whereNull('parent_id')->whereNull('deleted_at')->max('sort_order');
        $response = $this->actingAs($this->admin)->postJson('/api/admin/categories', [
            'name' => 'Defaults ' . $this->suffix,
            'slug' => 'defaults-' . $this->suffix,
        ]);
        $response->assertCreated()->assertJsonPath('data.status', 'active')->assertJsonPath('data.sort_order', $max + 1);
    }

    public function test_create_rejects_uppercase_space_and_underscore_slug(): void
    {
        foreach (['Upper-Case', 'has space', 'has_underscore'] as $slug) {
            $this->actingAs($this->admin)->postJson('/api/admin/categories', [
                'name' => 'Invalid slug', 'slug' => $slug,
            ])->assertUnprocessable()->assertJsonValidationErrors(['slug']);
        }
    }

    public function test_create_rejects_nonexistent_parent(): void
    {
        $this->actingAs($this->admin)->postJson('/api/admin/categories', [
            'name' => 'Bad parent', 'slug' => 'bad-parent-' . $this->suffix, 'parent_id' => 999999999,
        ])->assertUnprocessable()->assertJsonValidationErrors(['parent_id']);
    }

    public function test_update_requires_at_least_one_allowed_field(): void
    {
        $id = $this->createCategory();
        $this->actingAs($this->admin)->patchJson('/api/admin/categories/' . $id, [])
            ->assertUnprocessable()->assertJsonValidationErrors(['payload']);
    }

    public function test_update_rejects_slug_used_by_another_category(): void
    {
        $first = $this->createCategory(['slug' => 'duplicate-a-' . $this->suffix]);
        $second = $this->createCategory(['slug' => 'duplicate-b-' . $this->suffix]);
        $this->actingAs($this->admin)->patchJson('/api/admin/categories/' . $second, ['slug' => 'duplicate-a-' . $this->suffix])
            ->assertUnprocessable()->assertJsonValidationErrors(['slug']);
        $this->assertDatabaseHas('categories', ['id' => $first, 'slug' => 'duplicate-a-' . $this->suffix]);
        $this->assertDatabaseHas('categories', ['id' => $second, 'slug' => 'duplicate-b-' . $this->suffix]);
    }

    public function test_updating_soft_deleted_category_returns_404(): void
    {
        $id = $this->createCategory(['deleted_at' => now()]);
        $this->actingAs($this->admin)->patchJson('/api/admin/categories/' . $id, ['name' => 'Should not update'])->assertNotFound();
    }

    public function test_deleting_same_category_twice_returns_404_second_time(): void
    {
        $id = $this->createCategory();
        $this->actingAs($this->admin)->deleteJson('/api/admin/categories/' . $id)->assertOk();
        $this->actingAs($this->admin)->deleteJson('/api/admin/categories/' . $id)->assertNotFound();
    }

    public function test_restore_rejects_category_that_is_not_deleted(): void
    {
        $id = $this->createCategory();
        $this->actingAs($this->admin)->postJson('/api/admin/categories/' . $id . '/restore')->assertNotFound();
    }

    public function test_reorder_rejects_nonexistent_category_and_preserves_existing_row(): void
    {
        $id = $this->createCategory(['sort_order' => 7]);
        $this->actingAs($this->admin)->putJson('/api/admin/categories/reorder', [
            'items' => [['id' => 999999999, 'parent_id' => null, 'sort_order' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors(['items.0.id']);
        $this->assertDatabaseHas('categories', ['id' => $id, 'sort_order' => 7]);
    }

    public function test_non_admin_cannot_create_update_delete_restore_or_reorder(): void
    {
        $id = $this->createCategory();
        $requests = [
            fn () => $this->actingAs($this->learner)->postJson('/api/admin/categories', ['name' => 'Denied', 'slug' => 'denied-' . $this->suffix]),
            fn () => $this->actingAs($this->learner)->patchJson('/api/admin/categories/' . $id, ['name' => 'Denied']),
            fn () => $this->actingAs($this->learner)->deleteJson('/api/admin/categories/' . $id),
            fn () => $this->actingAs($this->learner)->postJson('/api/admin/categories/' . $id . '/restore'),
            fn () => $this->actingAs($this->learner)->putJson('/api/admin/categories/reorder', ['items' => [['id' => $id, 'parent_id' => null, 'sort_order' => 1]]]),
        ];
        foreach ($requests as $request) {
            $request()->assertForbidden();
        }
    }

    private function listIds(string $query = ''): array
    {
        return collect($this->actingAs($this->admin)->getJson('/api/admin/categories' . $query)
            ->assertOk()->json('data.items'))->pluck('id')->map(fn ($id): int => (int) $id)->all();
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
            'name' => 'Advanced Category ' . $this->suffix,
            'slug' => 'advanced-category-' . uniqid(),
            'description' => null,
            'sort_order' => 1,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ], $overrides));
    }
}