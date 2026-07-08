<?php
namespace App\Repositories\Instructor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
final class InstructorLearnerRepository
{
    public function courseBelongsToInstructor(int $courseId, int $instructorId): bool
    {
        return DB::table('courses')
            ->where('id', $courseId)
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->exists();
    }
    public function getSummary(int $instructorId, ?int $courseId = null): array
    {
        $query = DB::table('enrollments as e')
            ->join('courses as c', 'c.id', '=', 'e.course_id')
            ->where('c.instructor_id', $instructorId)
            ->whereNull('c.deleted_at')
            ->whereIn('e.status', ['active', 'completed']);
        if ($courseId !== null) {
            $query->where('e.course_id', $courseId);
        }
        $summary = $query
            ->selectRaw('COUNT(e.id) as total_enrollments')
            ->selectRaw("SUM(CASE WHEN e.status = 'active' THEN 1 ELSE 0 END) as active_enrollments")
            ->selectRaw("SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END) as completed_enrollments")
            ->first();
        return [
            'total_enrollments' => (int) ($summary->total_enrollments ?? 0),
            'active_enrollments' => (int) ($summary->active_enrollments ?? 0),
            'completed_enrollments' => (int) ($summary->completed_enrollments ?? 0),
        ];
    }
    public function paginateLearners(int $instructorId, array $filters): LengthAwarePaginator
    {
        $latestLessonProgress = DB::table('lesson_progress as lp')
            ->join('lessons as l', 'l.id', '=', 'lp.lesson_id')
            ->whereNull('l.deleted_at')
            ->select([
                'lp.user_id',
                'l.course_id',
                DB::raw('MAX(lp.last_accessed_at) as fallback_last_accessed_at'),
            ])
            ->groupBy('lp.user_id', 'l.course_id');
        $query = DB::table('enrollments as e')
            ->join('courses as c', 'c.id', '=', 'e.course_id')
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->leftJoinSub($latestLessonProgress, 'lp_latest', function ($join): void {
                $join->on('lp_latest.user_id', '=', 'e.user_id')
                    ->on('lp_latest.course_id', '=', 'e.course_id');
            })
            ->where('c.instructor_id', $instructorId)
            ->whereNull('c.deleted_at')
            ->whereNull('u.deleted_at')
            ->whereIn('e.status', ['active', 'completed']);
        if (!empty($filters['course_id'])) {
            $query->where('e.course_id', (int) $filters['course_id']);
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('e.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('u.full_name', 'like', "%{$search}%")
                    ->orWhere('u.email', 'like', "%{$search}%")
                    ->orWhere('c.title', 'like', "%{$search}%");
            });
        }
        if (!empty($filters['enrolled_from'])) {
            $query->whereDate('e.enrolled_at', '>=', $filters['enrolled_from']);
        }
        if (!empty($filters['enrolled_to'])) {
            $query->whereDate('e.enrolled_at', '<=', $filters['enrolled_to']);
        }
        $lastAccessedExpression = DB::raw('COALESCE(e.last_accessed_at, lp_latest.fallback_last_accessed_at)');
        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('e.enrolled_at'),
            'progress_asc' => $query->orderBy('e.progress_percent'),
            'progress_desc' => $query->orderByDesc('e.progress_percent'),
            'last_accessed_asc' => $query->orderBy($lastAccessedExpression),
            'last_accessed_desc' => $query->orderByDesc($lastAccessedExpression),
            default => $query->orderByDesc('e.enrolled_at'),
        };
        $query->select([
            'e.id as enrollment_id',
            'e.status as enrollment_status',
            'e.progress_percent',
            'e.enrolled_at',
            'e.completed_at',
            DB::raw('COALESCE(e.last_accessed_at, lp_latest.fallback_last_accessed_at) as last_accessed_at'),
            'u.id as learner_id',
            'u.full_name as learner_full_name',
            'u.email as learner_email',
            'c.id as course_id',
            'c.title as course_title',
        ]);
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);
        return $query->paginate($perPage)->appends($filters);
    }
    public function findEnrollmentForInstructor(int $enrollmentId, int $instructorId): ?object
    {
        return DB::table('enrollments as e')
            ->join('courses as c', 'c.id', '=', 'e.course_id')
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->where('e.id', $enrollmentId)
            ->where('c.instructor_id', $instructorId)
            ->whereNull('c.deleted_at')
            ->whereNull('u.deleted_at')
            ->select([
                'e.id as enrollment_id',
                'e.user_id as learner_id',
                'e.course_id',
                'e.status as enrollment_status',
                'e.progress_percent',
                'e.enrolled_at',
                'e.completed_at',
                'e.last_accessed_at',
                'u.full_name as learner_full_name',
                'u.email as learner_email',
                'c.title as course_title',
            ])
            ->first();
    }
    public function getLessonProgressRows(object $enrollment): Collection
    {
        return DB::table('lessons as l')
            ->join('course_sections as cs', 'cs.id', '=', 'l.course_section_id')
            ->leftJoin('lesson_progress as lp', function ($join) use ($enrollment): void {
                $join->on('lp.lesson_id', '=', 'l.id')
                    ->where('lp.user_id', '=', (int) $enrollment->learner_id);
            })
            ->leftJoin('video_progress as vp', function ($join) use ($enrollment): void {
                $join->on('vp.lesson_id', '=', 'l.id')
                    ->where('vp.user_id', '=', (int) $enrollment->learner_id);
            })
            ->where('l.course_id', (int) $enrollment->course_id)
            ->whereNull('l.deleted_at')
            ->whereNull('cs.deleted_at')
            ->orderBy('cs.sort_order')
            ->orderBy('l.sort_order')
            ->orderBy('l.id')
            ->select([
                'cs.id as section_id',
                'cs.title as section_title',
                'cs.sort_order as section_sort_order',
                'l.id as lesson_id',
                'l.title as lesson_title',
                'l.lesson_type',
                'l.sort_order as lesson_sort_order',
                'l.video_duration_seconds',
                DB::raw("COALESCE(lp.status, 'not_started') as progress_status"),
                'lp.started_at',
                'lp.completed_at',
                'lp.last_accessed_at',
                DB::raw('COALESCE(lp.learning_duration_seconds, 0) as learning_duration_seconds'),
                'vp.current_second',
            ])
            ->get();
    }
    public function getCourseOptions(int $instructorId): Collection
    {
        return DB::table('courses')
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->orderBy('title')
            ->select([
                'id',
                'title',
                'status',
            ])
            ->get();
    }
}