<?php

namespace App\Repositories\Instructor;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InstructorLearnerRepository
{
    public function paginateLearners(int $instructorId, array $filters): LengthAwarePaginator
    {
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);

        $query = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->join('users', 'users.id', '=', 'enrollments.user_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->whereIn('enrollments.status', ['active', 'completed'])
            ->select([
                'enrollments.id as enrollment_id',
                'users.id as learner_id',
                'users.full_name as learner_name',
                'users.email as learner_email',
                'courses.id as course_id',
                'courses.title as course_title',
                'enrollments.status',
                'enrollments.progress_percent',
                'enrollments.enrolled_at',
                'enrollments.completed_at',
                'enrollments.last_accessed_at',
            ]);

        if (!empty($filters['course_id'])) {
            $query->where('enrollments.course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('enrollments.status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search): void {
                $q->where('users.full_name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('courses.title', 'like', $search);
            });
        }

        if (!empty($filters['enrolled_from'])) {
            $query->whereDate('enrollments.enrolled_at', '>=', $filters['enrolled_from']);
        }

        if (!empty($filters['enrolled_to'])) {
            $query->whereDate('enrollments.enrolled_at', '<=', $filters['enrolled_to']);
        }

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('enrollments.enrolled_at'),
            'progress_asc' => $query->orderBy('enrollments.progress_percent'),
            'progress_desc' => $query->orderByDesc('enrollments.progress_percent'),
            default => $query->orderByDesc('enrollments.enrolled_at'),
        };

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}