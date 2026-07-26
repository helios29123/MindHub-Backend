<?php

namespace App\Repositories\Report;

use Illuminate\Support\Facades\DB;

class InstructorTopCourseRepository
{
    public function getTopCourses(int $instructorId, array $filters): array
    {
        $limit = min(max((int) ($filters['limit'] ?? 5), 1), 20);

        $coursesQuery = DB::table('courses')
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at');

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $statusInput = strtolower(trim((string) $filters['status']));
            if ($statusInput === 'published' || $statusInput === 'active') {
                $coursesQuery->whereIn('status', ['published', 'approved', 'active']);
            } elseif ($statusInput === 'pending' || $statusInput === 'pending_review') {
                $coursesQuery->whereIn('status', ['pending', 'pending_review', 'submitted']);
            } elseif ($statusInput === 'hidden') {
                $coursesQuery->whereIn('status', ['hidden', 'inactive']);
            } else {
                $coursesQuery->where('status', $statusInput);
            }
        }

        $courses = $coursesQuery->get(['id', 'title', 'status', 'thumbnail_url', 'level', 'price']);
        $courseIds = $courses->pluck('id')->toArray();

        if (empty($courseIds)) {
            return [];
        }

        $enrollmentsQuery = DB::table('enrollments')
            ->whereIn('course_id', $courseIds)
            ->whereIn('status', ['active', 'completed']);

        if (!empty($filters['date_from'])) {
            $enrollmentsQuery->whereDate('enrolled_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $enrollmentsQuery->whereDate('enrolled_at', '<=', $filters['date_to']);
        }

        $enrollmentsMap = $enrollmentsQuery
            ->select('course_id', DB::raw('COUNT(id) as enrollment_count'), DB::raw('COUNT(DISTINCT user_id) as unique_learner_count'))
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $revenuesQuery = DB::table('revenues')
            ->whereIn('course_id', $courseIds)
            ->whereIn('status', ['available', 'withdrawn']);

        if (!empty($filters['date_from'])) {
            $revenuesQuery->whereDate('earned_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $revenuesQuery->whereDate('earned_at', '<=', $filters['date_to']);
        }

        $revenuesMap = $revenuesQuery
            ->select('course_id', DB::raw('COALESCE(SUM(instructor_amount), 0) as total_instructor_revenue'), DB::raw('COALESCE(SUM(gross_amount), 0) as total_gross_revenue'))
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $items = [];
        foreach ($courses as $c) {
            $e = $enrollmentsMap->get($c->id);
            $r = $revenuesMap->get($c->id);

            $eCount = $e ? (int) $e->enrollment_count : 0;
            $uCount = $e ? (int) $e->unique_learner_count : 0;
            $instRev = $r ? (float) $r->total_instructor_revenue : 0.0;
            $grossRev = $r ? (float) $r->total_gross_revenue : 0.0;

            $thumbnail = $c->thumbnail_url;
            if ($thumbnail && !str_starts_with($thumbnail, 'http://') && !str_starts_with($thumbnail, 'https://')) {
                $thumbnail = url($thumbnail);
            }

            $items[] = [
                'id' => (int) $c->id,
                'course_id' => (int) $c->id,
                'title' => $c->title,
                'status' => $c->status,
                'thumbnail_url' => $thumbnail,
                'image' => $thumbnail,
                'level' => $c->level ?? 'beginner',
                'enrollment_count' => $eCount,
                'enrollments_count' => $eCount,
                'studentCount' => $uCount,
                'student_count' => $uCount,
                'learners_count' => $uCount,
                'unique_learner_count' => $uCount,
                'revenue' => $instRev,
                'instructor_revenue' => $instRev,
                'gross_revenue' => $grossRev,
                'price' => (float) ($c->price ?? 0),
            ];
        }

        usort($items, function ($a, $b) {
            if ($b['enrollment_count'] !== $a['enrollment_count']) {
                return $b['enrollment_count'] <=> $a['enrollment_count'];
            }
            return $b['revenue'] <=> $a['revenue'];
        });

        $topItems = array_slice($items, 0, $limit);
        foreach ($topItems as $idx => &$item) {
            $item['rank'] = $idx + 1;
        }

        return $topItems;
    }
}