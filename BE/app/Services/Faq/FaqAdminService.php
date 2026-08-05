<?php

namespace App\Services\Faq;

use App\Models\Faq;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class FaqAdminService
{
    /**
     * Get paginated FAQs with filters, sorting, and summary metrics.
     */
    public function getFaqs(array $params): array
    {
        $query = Faq::query();

        // 1. Filter: Search
        if (!empty($params['search'])) {
            $search = '%' . $params['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', $search)
                  ->orWhere('answer', 'LIKE', $search);
            });
        }

        // 2. Filter: Type
        if (!empty($params['type']) && $params['type'] !== 'all') {
            $query->where('type', $params['type']);
        }

        // 3. Filter: Status
        if (!empty($params['status']) && $params['status'] !== 'all') {
            $query->where('status', $params['status']);
        }

        // 4. Filter: Scope (general/linked/unlinked)
        if (!empty($params['scope']) && $params['scope'] !== 'all') {
            if ($params['scope'] === 'general' || $params['scope'] === 'unlinked') {
                $query->whereDoesntHave('courses');
            } elseif ($params['scope'] === 'linked') {
                $query->whereHas('courses');
            }
        }

        // 5. Sorting
        $sortBy = $params['sort_by'] ?? 'sort_order';
        if ($sortBy === 'course_count') {
            $sortBy = 'courses_count';
        }
        $sortDir = $params['sort_direction'] ?? 'asc';
        
        // Allowed sort columns validation
        if (!in_array($sortBy, ['id', 'question', 'sort_order', 'updated_at', 'created_at', 'courses_count'])) {
            $sortBy = 'sort_order';
        }
        if (!in_array(strtolower($sortDir), ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $query->orderBy($sortBy, $sortDir);

        // 6. Pagination
        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 20;
        if ($perPage < 1) $perPage = 20;
        if ($perPage > 500) $perPage = 500;

        // Load relations and counts
        $query->with(['courses' => function ($q) {
            $q->select('courses.id', 'courses.title');
        }])->withCount('courses');

        $paginator = $query->paginate($perPage);

        // 7. Calculate Summary KPIs
        $summary = $this->calculateSummary();

        // 8. Transform/Map items
        $items = collect($paginator->items())->map(function ($faq) {
            return [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'type' => $faq->type,
                'status' => $faq->status,
                'sort_order' => $faq->sort_order,
                'created_at' => $faq->created_at ? $faq->created_at->toIso8601String() : null,
                'updated_at' => $faq->updated_at ? $faq->updated_at->toIso8601String() : null,
                'course_count' => $faq->courses_count,
                'linked_courses' => $faq->courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                    ];
                })->toArray(),
            ];
        })->toArray();

        return [
            'summary' => $summary,
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ];
    }

    /**
     * Get detail of a single FAQ.
     */
    public function getFaqDetail(int $id): ?array
    {
        $faq = Faq::with(['courses' => function ($q) {
            $q->select('courses.id', 'courses.title');
        }])->withCount('courses')->find($id);

        if (!$faq) {
            return null;
        }

        return [
            'id' => $faq->id,
            'question' => $faq->question,
            'answer' => $faq->answer,
            'type' => $faq->type,
            'status' => $faq->status,
            'sort_order' => $faq->sort_order,
            'created_at' => $faq->created_at ? $faq->created_at->toIso8601String() : null,
            'updated_at' => $faq->updated_at ? $faq->updated_at->toIso8601String() : null,
            'course_count' => $faq->courses_count,
            'linked_courses' => $faq->courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                ];
            })->toArray(),
        ];
    }

    /**
     * Create a new FAQ.
     */
    public function createFaq(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $faq = Faq::create([
                'question' => $data['question'],
                'answer' => $data['answer'],
                'type' => $data['type'],
                'status' => $data['status'] ?? 'active',
                'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            ]);

            if (isset($data['course_ids']) && is_array($data['course_ids'])) {
                $this->customSyncCourses($faq, $data['course_ids']);
            }

            return $this->getFaqDetail($faq->id);
        });
    }

    /**
     * Update an existing FAQ.
     */
    public function updateFaq(int $id, array $data): ?array
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return null;
        }

        return DB::transaction(function () use ($faq, $data) {
            $faq->update(array_filter([
                'question' => $data['question'] ?? null,
                'answer' => $data['answer'] ?? null,
                'type' => $data['type'] ?? null,
                'status' => $data['status'] ?? null,
                'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            ], fn($v) => $v !== null));

            if (isset($data['course_ids']) && is_array($data['course_ids'])) {
                $this->customSyncCourses($faq, $data['course_ids']);
            }

            return $this->getFaqDetail($faq->id);
        });
    }

    /**
     * Delete an existing FAQ.
     */
    public function deleteFaq(int $id): bool
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return false;
        }

        return DB::transaction(function () use ($faq) {
            // Free up sort_order and soft-delete pivot links
            DB::table('course_faqs')
                ->where('faq_id', $faq->id)
                ->whereNull('deleted_at')
                ->update([
                    'sort_order' => DB::raw('faq_id + 1000000'),
                    'deleted_at' => now()
                ]);

            return (bool) $faq->delete();
        });
    }

    /**
     * Bulk reorder FAQs
     * @param array $items Array of ['id' => id, 'sort_order' => order]
     * @return array
     */
    public function reorderFaqs(array $items): array
    {
        try {
            DB::beginTransaction();
            foreach ($items as $item) {
                Faq::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }
            DB::commit();
            return ['success' => true, 'message' => 'Cập nhật thứ tự thành công.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật thứ tự FAQs: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'];
        }
    }

    /**
     * Sync/link courses for a FAQ.
     */
    public function syncFaqCourses(int $id, array $courseIds): ?array
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return null;
        }

        return DB::transaction(function () use ($faq, $courseIds) {
            $this->customSyncCourses($faq, $courseIds);
            return $this->getFaqDetail($faq->id);
        });
    }

    /**
     * Custom sync courses to support soft delete and prevent sort_order collisions.
     */
    private function customSyncCourses($faq, array $courseIds): void
    {
        $now = now();
        
        // 1. Get existing pivot records (including soft-deleted ones)
        $existing = DB::table('course_faqs')
            ->where('faq_id', $faq->id)
            ->get()
            ->keyBy('course_id');

        // 2. Determine which courses to detach (soft delete)
        foreach ($existing as $courseId => $pivot) {
            if (!in_array($courseId, $courseIds) && $pivot->deleted_at === null) {
                DB::table('course_faqs')
                    ->where('faq_id', $faq->id)
                    ->where('course_id', $courseId)
                    ->update([
                        'sort_order' => $faq->id + 1000000 + $courseId, // ensure uniqueness
                        'deleted_at' => $now
                    ]);
            }
        }

        // 3. Determine which courses to attach / restore / update
        foreach ($courseIds as $index => $courseId) {
            if (isset($existing[$courseId])) {
                // If it exists (active or soft-deleted), restore it and update sort_order
                DB::table('course_faqs')
                    ->where('faq_id', $faq->id)
                    ->where('course_id', $courseId)
                    ->update([
                        'sort_order' => $index,
                        'deleted_at' => null,
                        'created_at' => $existing[$courseId]->created_at ?? $now
                    ]);
            } else {
                // Insert new link
                DB::table('course_faqs')->insert([
                    'faq_id' => $faq->id,
                    'course_id' => $courseId,
                    'sort_order' => $index,
                    'created_at' => $now,
                    'deleted_at' => null
                ]);
            }
        }
    }

    /**
     * Calculate summary metrics.
     */
    private function calculateSummary(): array
    {
        $total = Faq::count();
        $active = Faq::where('status', 'active')->count();
        $inactive = Faq::where('status', 'inactive')->count();
        
        // Count FAQs that have no active pivot links
        $unlinked = Faq::whereDoesntHave('courses')->count();

        // Count unique courses that have at least one active FAQ linked
        $linkedCourses = DB::table('course_faqs')
            ->whereNull('deleted_at')
            ->distinct('course_id')
            ->count('course_id');

        return [
            'total_faqs' => $total,
            'active_count' => $active,
            'inactive_count' => $inactive,
            'unlinked_count' => $unlinked,
            'linked_course_count' => $linkedCourses,
        ];
    }
}
