<?php

namespace App\Repositories\Report;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstructorDashboardAlertRepository
{
    public function getAlerts(int $instructorId, array $filters): array
    {
        $limit = min(max((int) ($filters['limit'] ?? 7), 1), 10);
        $alerts = [];

        if (Schema::hasTable('notifications')) {
            $query = DB::table('notifications')
                ->where('user_id', $instructorId)
                ->orderByDesc('created_at')
                ->limit($limit);

            if (Schema::hasColumn('notifications', 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            $alerts = $query->get()
                ->map(fn ($row) => [
                    'type' => $row->type,
                    'title' => $row->title,
                    'message' => $row->message,
                    'created_at' => $row->created_at,
                    'action_url' => property_exists($row, 'action_url') ? $row->action_url : null,
                    'read_at' => property_exists($row, 'read_at') ? $row->read_at : null,
                ])
                ->all();

            if (count($alerts) >= $limit) {
                return $alerts;
            }
        }

        return array_slice(array_merge($alerts, $this->fallbackAlerts($instructorId)), 0, $limit);
    }

    private function fallbackAlerts(int $instructorId): array
    {
        $alerts = [];

        $question = DB::table('comments as q')
            ->join('users as learner', 'learner.id', '=', 'q.user_id')
            ->join('lessons', 'lessons.id', '=', 'q.lesson_id')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->whereNull('q.parent_id')
            ->where('q.status', 'visible')
            ->where('learner.role', 'learner')
            ->orderByDesc('q.created_at')
            ->select('q.created_at', 'courses.title as course_title', 'learner.full_name')
            ->first();

        if ($question) {
            $alerts[] = [
                'type' => 'unanswered_question',
                'title' => 'Dữ liệu không hợp lệ.',
                'message' => $question->full_name . 'Dữ liệu không hợp lệ.' . $question->course_title . '.',
                'created_at' => $question->created_at,
                'action_url' => '/instructor/questions?status=unanswered',
                'read_at' => null,
            ];
        }

        $rejectedCourse = DB::table('courses')
            ->where('instructor_id', $instructorId)
            ->where('status', 'rejected')
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->first();

        if ($rejectedCourse) {
            $alerts[] = [
                'type' => 'course_rejected',
                'title' => 'Dữ liệu không hợp lệ.',
                'message' => 'Khﾃｳa ' . $rejectedCourse->title . 'Dữ liệu không hợp lệ.',
                'created_at' => $rejectedCourse->updated_at,
                'action_url' => '/instructor/courses?status=rejected',
                'read_at' => null,
            ];
        }

        $withdraw = DB::table('withdraw_requests')
            ->where('user_id', $instructorId)
            ->orderByDesc('updated_at')
            ->first();

        if ($withdraw) {
            $alerts[] = [
                'type' => 'withdraw_request',
                'title' => 'Dữ liệu không hợp lệ.',
                'message' => 'Dữ liệu không hợp lệ.' . $withdraw->id . 'Dữ liệu không hợp lệ.' . $withdraw->status . '.',
                'created_at' => $withdraw->updated_at,
                'action_url' => '/instructor/withdrawals',
                'read_at' => null,
            ];
        }

        usort($alerts, fn ($a, $b) => strcmp((string) $b['created_at'], (string) $a['created_at']));

        return $alerts;
    }
}