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
                ->map(fn ($row, $idx) => [
                    'id' => (int) ($row->id ?? ($idx + 1)),
                    'type' => $row->type ?? 'info',
                    'title' => $row->title ?? 'Thông báo',
                    'message' => $row->message ?? '',
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
            
            ->whereNull('q.parent_id')
            ->where('q.status', 'visible')
            ->where('learner.role', 'learner')
            ->orderByDesc('q.created_at')
            ->select('q.created_at', 'courses.title as course_title', 'learner.full_name')
            ->first();

        if ($question) {
            $alerts[] = [
                'id' => 101,
                'type' => 'unanswered_question',
                'title' => 'Câu hỏi mới từ học viên',
                'message' => 'Học viên ' . $question->full_name . ' vừa đặt câu hỏi trong khóa ' . $question->course_title . '.',
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
                'id' => 102,
                'type' => 'course_rejected',
                'title' => 'Khóa học chưa được phê duyệt',
                'message' => 'Khóa học "' . $rejectedCourse->title . '" đã bị từ chối phê duyệt.',
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
                'id' => 103,
                'type' => 'withdraw_request',
                'title' => 'Cập nhật yêu cầu rút tiền',
                'message' => 'Yêu cầu rút tiền #' . $withdraw->id . ' đang ở trạng thái ' . $withdraw->status . '.',
                'created_at' => $withdraw->updated_at,
                'action_url' => '/instructor/withdrawals',
                'read_at' => null,
            ];
        }

        usort($alerts, fn ($a, $b) => strcmp((string) $b['created_at'], (string) $a['created_at']));

        return $alerts;
    }
}