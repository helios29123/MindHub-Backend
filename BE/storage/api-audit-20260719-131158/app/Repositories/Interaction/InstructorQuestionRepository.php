<?php

namespace App\Repositories\Interaction;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InstructorQuestionRepository
{
    public function paginateQuestions(int $instructorId, array $filters): LengthAwarePaginator
    {
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);

        $query = DB::table('comments as q')
            ->join('users as learner', 'learner.id', '=', 'q.user_id')
            ->join('lessons', 'lessons.id', '=', 'q.lesson_id')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->leftJoin('comments as replies', function ($join): void {
                $join->on('replies.parent_id', '=', 'q.id')
                    ->where('replies.status', '=', 'visible');
            })
            ->leftJoin('comments as instructor_replies', function ($join) use ($instructorId): void {
                $join->on('instructor_replies.parent_id', '=', 'q.id')
                    ->where('instructor_replies.status', '=', 'visible')
                    ->where('instructor_replies.user_id', '=', $instructorId);
            })
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->whereNull('q.parent_id')
            ->where('q.status', 'visible')
            ->where('learner.role', 'learner')
            ->selectRaw('
                q.id,
                q.content,
                q.created_at,
                learner.id as learner_id,
                learner.full_name as learner_name,
                learner.email as learner_email,
                courses.id as course_id,
                courses.title as course_title,
                lessons.id as lesson_id,
                lessons.title as lesson_title,
                COUNT(DISTINCT replies.id) as reply_count,
                COUNT(DISTINCT instructor_replies.id) as instructor_reply_count,
                CASE WHEN COUNT(DISTINCT instructor_replies.id) > 0 THEN "answered" ELSE "unanswered" END as question_status
            ')
            ->groupBy(
                'q.id',
                'q.content',
                'q.created_at',
                'learner.id',
                'learner.full_name',
                'learner.email',
                'courses.id',
                'courses.title',
                'lessons.id',
                'lessons.title'
            );

        if (!empty($filters['course_id'])) {
            $query->where('courses.id', (int) $filters['course_id']);
        }

        if (!empty($filters['lesson_id'])) {
            $query->where('lessons.id', (int) $filters['lesson_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search): void {
                $q->where('q.content', 'like', $search)
                    ->orWhere('learner.full_name', 'like', $search)
                    ->orWhere('courses.title', 'like', $search)
                    ->orWhere('lessons.title', 'like', $search);
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('q.created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('q.created_at', '<=', $filters['date_to']);
        }

        if (($filters['status'] ?? null) === 'answered') {
            $query->havingRaw('COUNT(DISTINCT instructor_replies.id) > 0');
        }

        if (($filters['status'] ?? null) === 'unanswered') {
            $query->havingRaw('COUNT(DISTINCT instructor_replies.id) = 0');
        }

        return $query->orderByDesc('q.created_at')->paginate($perPage, ['*'], 'page', $page);
    }
}
