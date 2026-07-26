<?php

namespace App\Repositories\Instructor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Course;

final class InstructorCourseRepository
{
    public function create(array $courseData): Course
    {
        return Course::create($courseData);
    }

    public function syncCategories(Course $course, array $categoryIds): void
    {
        $course->categories()->sync($categoryIds);
    }

    public function findWithCategories(int $courseId): Course
    {
        return Course::query()
            ->with(['categories'])
            ->findOrFail($courseId);
    }    public function findByIdWithReviewRelations(int $courseId): ?Course
    {
        return Course::query()
            ->with(['categories', 'sections.lessons'])
            ->find($courseId);
    }
    public function markAsPendingReview(Course $course): Course
    {
        $course->forceFill([
            'status' => 'pending_review',
            'admin_reject_reason' => null,
        ])->save();
        return $this->findByIdWithReviewRelations((int) $course->id)
            ?? $course->fresh(['categories', 'sections.lessons'])
            ?? $course;
    }
    public function findCourseForChecklist(int $courseId): ?object
    {
        return \Illuminate\Support\Facades\DB::table('courses')
            ->where('id', $courseId)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'instructor_id',
                'title',
                'short_description',
                'description',
                'thumbnail_url',
                'intro_video_url',
                'price',
                'sale_price',
                'status',
                'created_at',
                'updated_at',
            ])
            ->first();
    }

    public function getChecklistCategories(int $courseId): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('course_categories as cc')
            ->join('categories as c', 'c.id', '=', 'cc.category_id')
            ->where('cc.course_id', $courseId)
            ->whereNull('c.deleted_at')
            ->where(function ($query): void {
                $query->whereNull('c.status')
                    ->orWhere('c.status', 'active')
                    ->orWhere('c.status', 'published');
            })
            ->select([
                'c.id',
                'c.name',
                'c.status',
            ])
            ->get();
    }

    public function getChecklistSections(int $courseId): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('course_sections')
            ->where('course_id', $courseId)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'course_id',
                'title',
                'description',
                'sort_order',
                'status',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getChecklistLessons(int $courseId): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('lessons as l')
            ->leftJoin('course_sections as cs', 'cs.id', '=', 'l.course_section_id')
            ->where('l.course_id', $courseId)
            ->whereNull('l.deleted_at')
            ->where(function ($query): void {
                $query->whereNull('cs.deleted_at')
                    ->orWhereNull('l.course_section_id');
            })
            ->select([
                'l.id',
                'l.course_id',
                'l.course_section_id',
                'cs.status as section_status',
                'l.title',
                'l.lesson_type',
                'l.content',
                'l.video_url',
                'l.video_duration_seconds',
                'l.status',
                'l.sort_order',
            ])
            ->orderBy('cs.sort_order')
            ->orderBy('l.sort_order')
            ->orderBy('l.id')
            ->get();
    }

    public function countChecklistLessonAssets(int $courseId): int
    {
        return (int) \Illuminate\Support\Facades\DB::table('lesson_assets as la')
            ->join('lessons as l', 'l.id', '=', 'la.lesson_id')
            ->where('l.course_id', $courseId)
            ->whereNull('l.deleted_at')
            ->whereNull('la.deleted_at')
            ->count();
    }

    public function getChecklistQuizzes(int $courseId): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('quizzes')
            ->where('course_id', $courseId)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'course_id',
                'lesson_id',
                'title',
                'description',
                'passing_score',
                'status',
            ])
            ->orderBy('id')
            ->get();
    }

    public function getChecklistQuizQuestionStats(int $courseId): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('quiz_questions as qq')
            ->join('quizzes as q', 'q.id', '=', 'qq.quiz_id')
            ->leftJoin('quiz_options as qo', 'qo.question_id', '=', 'qq.id')
            ->where('q.course_id', $courseId)
            ->whereNull('q.deleted_at')
            ->where('q.status', 'published')
            ->select([
                'qq.id',
                'qq.quiz_id',
                'qq.question_text',
                'qq.question_type',
                'qq.score',
                'qq.sort_order',
                \Illuminate\Support\Facades\DB::raw('COUNT(qo.id) as options_count'),
                \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qo.is_correct = 1 THEN 1 ELSE 0 END) as correct_options_count'),
            ])
            ->groupBy([
                'qq.id',
                'qq.quiz_id',
                'qq.question_text',
                'qq.question_type',
                'qq.score',
                'qq.sort_order',
            ])
            ->orderBy('qq.quiz_id')
            ->orderBy('qq.sort_order')
            ->orderBy('qq.id')
            ->get();
    }

public function paginateCourses(int $instructorId, array $filters): LengthAwarePaginator
    {
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);

        $query = DB::table('courses')
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at');

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $statusInput = strtolower(trim((string) $filters['status']));
            if ($statusInput === 'published' || $statusInput === 'active') {
                $query->whereIn('status', ['published', 'approved', 'active']);
            } elseif ($statusInput === 'pending' || $statusInput === 'pending_review') {
                $query->whereIn('status', ['pending', 'pending_review', 'submitted']);
            } elseif ($statusInput === 'hidden') {
                $query->whereIn('status', ['hidden', 'inactive']);
            } else {
                $query->where('status', $statusInput);
            }
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where('title', 'like', $search);
        }

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('created_at'),
            'title_asc' => $query->orderBy('title'),
            'title_desc' => $query->orderByDesc('title'),
            default => $query->orderByDesc('created_at'),
        };

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $courseIds = collect($paginator->items())->pluck('id')->all();

        if (empty($courseIds)) {
            return $paginator;
        }

        // 1. Enrollment count
        $enrollmentCounts = DB::table('enrollments')
            ->whereIn('course_id', $courseIds)
            ->whereIn('status', ['active', 'completed', 'enrolled'])
            ->groupBy('course_id')
            ->select('course_id', DB::raw('COUNT(id) as count'))
            ->pluck('count', 'course_id')
            ->all();

        // 2. Revenue amount
        $revenuesMap = DB::table('revenues')
            ->whereIn('course_id', $courseIds)
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', ['cancelled']);
            })
            ->groupBy('course_id')
            ->select('course_id', DB::raw('COALESCE(SUM(instructor_amount), 0) as total'))
            ->pluck('total', 'course_id')
            ->all();

        // 3. Rating & Review Count
        $hasCourseReviewsTable = Schema::hasTable('course_reviews');
        $reviewsMap = collect();

        if ($hasCourseReviewsTable) {
            $reviewsMap = DB::table('course_reviews')
                ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
                ->whereIn('orders.course_id', $courseIds)
                ->whereNull('course_reviews.deleted_at')
                ->groupBy('orders.course_id')
                ->select(
                    'orders.course_id',
                    DB::raw('COUNT(course_reviews.id) as count'),
                    DB::raw('ROUND(AVG(course_reviews.rating), 1) as avg_rating')
                )
                ->get()
                ->keyBy('course_id');
        } elseif (Schema::hasTable('reviews')) {
            $reviewsMap = DB::table('reviews')
                ->whereIn('course_id', $courseIds)
                ->whereNull('deleted_at')
                ->groupBy('course_id')
                ->select(
                    'course_id',
                    DB::raw('COUNT(id) as count'),
                    DB::raw('ROUND(AVG(rating), 1) as avg_rating')
                )
                ->get()
                ->keyBy('course_id');
        }

        // 4. Categories Map
        $categoriesMap = DB::table('course_categories as cc')
            ->join('categories as c', 'c.id', '=', 'cc.category_id')
            ->whereIn('cc.course_id', $courseIds)
            ->whereNull('c.deleted_at')
            ->select('cc.course_id', 'c.id', 'c.name')
            ->get()
            ->groupBy('course_id');

        foreach ($paginator->items() as $course) {
            $cId = (int) $course->id;
            $eCount = (int) ($enrollmentCounts[$cId] ?? 0);
            $revAmount = (float) ($revenuesMap[$cId] ?? 0);
            $revItem = $reviewsMap->get($cId);
            $revCount = $revItem ? (int) $revItem->count : 0;
            $ratingVal = $revItem ? (float) $revItem->avg_rating : 0.0;

            $course->enrollment_count = $eCount;
            $course->enrollments_count = $eCount;
            $course->revenue = number_format($revAmount, 2, '.', '');
            $course->revenue_amount = $revAmount;
            $course->rating = $ratingVal;
            $course->review_count = $revCount;
            $course->reviews_count = $revCount;

            $catList = $categoriesMap->get($cId, collect());
            $course->categories = $catList->map(fn ($cat): array => [
                'id' => (int) $cat->id,
                'name' => $cat->name,
            ])->values()->all();
        }

        return $paginator;
    }

public function instructorOwnsCourse(int $instructorId, int $courseId): bool
    {
        return DB::table('courses')
            ->where('id', $courseId)
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->exists();
    }


    public function findOwnedCourseForDetail(int $courseId, int $instructorId): ?Course
    {
        $course = Course::query()
            ->with(['categories'])
            ->where('id', $courseId)
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->first();

        if (!$course) {
            return null;
        }

        $course->setAttribute('section_count', (int) DB::table('course_sections')
            ->where('course_id', $courseId)
            ->whereNull('deleted_at')
            ->count());

        $course->setAttribute('lesson_count', (int) DB::table('lessons')
            ->where('course_id', $courseId)
            ->whereNull('deleted_at')
            ->count());

        $course->setAttribute('asset_count', (int) DB::table('lesson_assets as la')
            ->join('lessons as l', 'l.id', '=', 'la.lesson_id')
            ->where('l.course_id', $courseId)
            ->whereNull('l.deleted_at')
            ->whereNull('la.deleted_at')
            ->count());

        $course->setAttribute('preview_lesson_count', (int) DB::table('lessons')
            ->where('course_id', $courseId)
            ->whereNull('deleted_at')
            ->where('is_preview', true)
            ->count());

        $course->setAttribute('enrollment_count', (int) DB::table('enrollments')
            ->where('course_id', $courseId)
            ->count());

        $course->setAttribute('revenue_amount', (float) DB::table('revenues')
            ->where('course_id', $courseId)
            ->sum('instructor_amount'));

        return $course;
    }

    public function findOwnedCourseForContent(int $courseId, int $instructorId): ?Course
    {
        return Course::query()
            ->with([
                'sections' => function ($query): void {
                    $query->whereNull('deleted_at')
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
                'sections.lessons' => function ($query): void {
                    $query->whereNull('deleted_at')
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
                'sections.lessons.assets' => function ($query): void {
                    $query->whereNull('deleted_at')
                        ->orderBy('id');
                },
            ])
            ->where('id', $courseId)
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->first();
    }

    public function updateCourseWithCategories(Course $course, array $data, ?array $categoryIds = null): Course
    {
        $course->fill($data);
        $course->save();

        if ($categoryIds !== null) {
            $course->categories()->sync($categoryIds);
        }

        return $course->refresh()->load('categories');
    }

}
