<?php

namespace App\Repositories\Instructor;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            
            ->whereIn('enrollments.status', ['active', 'completed'])
            ->select([
                'enrollments.id as enrollment_id',
                'users.id as learner_id',
                'users.full_name as learner_name',
                'users.email as learner_email',
                'users.avatar_url as learner_avatar_url',
                'courses.id as course_id',
                'courses.title as course_title',
                'enrollments.status',
                'enrollments.progress_percent',
                'enrollments.enrolled_at',
                'enrollments.completed_at',
                'enrollments.last_accessed_at',
            ]);

        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all') {
            $query->where('enrollments.course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'learning') {
                $query->where(function ($q) {
                    $q->where('enrollments.status', 'active')
                      ->where('enrollments.progress_percent', '<', 100);
                });
            } elseif ($filters['status'] === 'completed') {
                $query->where(function ($q) {
                    $q->where('enrollments.status', 'completed')
                      ->orWhere('enrollments.progress_percent', '>=', 100);
                });
            } else {
                $query->where('enrollments.status', $filters['status']);
            }
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search): void {
                $q->where('users.full_name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('courses.title', 'like', $search);
            });
        }

        if (!empty($filters['preset']) || !empty($filters['date_from']) || !empty($filters['enrolled_from'])) {
            $period = $this->resolvePeriod($filters);
            $query->whereBetween('enrollments.enrolled_at', [$period['current_from'], $period['current_to']]);
        }

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('enrollments.enrolled_at'),
            'progress_asc' => $query->orderBy('enrollments.progress_percent'),
            'progress_desc' => $query->orderByDesc('enrollments.progress_percent'),
            default => $query->orderByDesc('enrollments.enrolled_at'),
        };

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function resolvePeriod(array $filters): array
    {
        $preset = $filters['preset'] ?? '30d';
        $now = now();

        switch ($preset) {
            case '7d':
                $currentFrom = (clone $now)->subDays(6)->startOfDay();
                $currentTo = (clone $now)->endOfDay();
                $daysCount = 7;
                $previousFrom = (clone $currentFrom)->subDays(7)->startOfDay();
                $previousTo = (clone $currentFrom)->subDays(1)->endOfDay();
                $label = 'So với 7 ngày liền trước';
                $granularity = 'day';
                break;

            case '90d':
                $currentFrom = (clone $now)->subDays(89)->startOfDay();
                $currentTo = (clone $now)->endOfDay();
                $daysCount = 90;
                $previousFrom = (clone $currentFrom)->subDays(90)->startOfDay();
                $previousTo = (clone $currentFrom)->subDays(1)->endOfDay();
                $label = 'So với 90 ngày liền trước';
                $granularity = 'day';
                break;

            case 'this_month':
                $currentFrom = (clone $now)->startOfMonth()->startOfDay();
                $currentTo = (clone $now)->endOfDay();
                $daysCount = (int) $currentFrom->diffInDays($currentTo) + 1;
                $previousFrom = (clone $now)->subMonthNoOverflow()->startOfMonth()->startOfDay();
                $previousTo = (clone $now)->subMonthNoOverflow()->endOfMonth()->endOfDay();
                $label = 'So với tháng trước';
                $granularity = 'day';
                break;

            case 'last_month':
                $currentFrom = (clone $now)->subMonthNoOverflow()->startOfMonth()->startOfDay();
                $currentTo = (clone $now)->subMonthNoOverflow()->endOfMonth()->endOfDay();
                $daysCount = (int) $currentFrom->diffInDays($currentTo) + 1;
                $previousFrom = (clone $now)->subMonthsNoOverflow(2)->startOfMonth()->startOfDay();
                $previousTo = (clone $now)->subMonthsNoOverflow(2)->endOfMonth()->endOfDay();
                $label = 'So với tháng trước nữa';
                $granularity = 'day';
                break;

            case 'this_year':
                $currentFrom = (clone $now)->startOfYear()->startOfDay();
                $currentTo = (clone $now)->endOfDay();
                $daysCount = (int) $currentFrom->diffInDays($currentTo) + 1;
                $previousFrom = (clone $now)->subYear()->startOfYear()->startOfDay();
                $previousTo = (clone $now)->subYear()->endOfYear()->endOfDay();
                $label = 'So với cùng kỳ năm trước';
                $granularity = 'month';
                break;

            case 'custom':
                $dateFrom = $filters['date_from'] ?? $filters['enrolled_from'] ?? null;
                $dateTo = $filters['date_to'] ?? $filters['enrolled_to'] ?? null;

                if (!empty($dateFrom) && !empty($dateTo)) {
                    $currentFrom = \Illuminate\Support\Carbon::parse($dateFrom)->startOfDay();
                    $currentTo = \Illuminate\Support\Carbon::parse($dateTo)->endOfDay();
                } else {
                    $currentFrom = (clone $now)->subDays(29)->startOfDay();
                    $currentTo = (clone $now)->endOfDay();
                }
                $daysCount = max(1, (int) $currentFrom->diffInDays($currentTo) + 1);
                $previousFrom = (clone $currentFrom)->subDays($daysCount)->startOfDay();
                $previousTo = (clone $currentFrom)->subDays(1)->endOfDay();
                $label = "So với {$daysCount} ngày liền trước";
                $granularity = $daysCount > 90 ? 'month' : 'day';
                break;

            case '30d':
            default:
                $currentFrom = (clone $now)->subDays(29)->startOfDay();
                $currentTo = (clone $now)->endOfDay();
                $daysCount = 30;
                $previousFrom = (clone $currentFrom)->subDays(30)->startOfDay();
                $previousTo = (clone $currentFrom)->subDays(1)->endOfDay();
                $label = 'So với 30 ngày liền trước';
                $granularity = 'day';
                break;
        }

        return [
            'preset' => $preset,
            'current_from' => $currentFrom,
            'current_to' => $currentTo,
            'previous_from' => $previousFrom,
            'previous_to' => $previousTo,
            'days_count' => $daysCount,
            'comparison_label' => $label,
            'granularity' => $granularity,
        ];
    }

    public function getLearnersSummary(int $instructorId, array $filters = []): array
    {
        $period = $this->resolvePeriod($filters);

        $baseQuery = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->where('courses.instructor_id', $instructorId)
            
            ->whereIn('enrollments.status', ['active', 'completed']);

        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all') {
            $baseQuery->where('enrollments.course_id', (int) $filters['course_id']);
        }

        $totalEnrollments = (clone $baseQuery)->count();
        
        $activeCount = (clone $baseQuery)
            ->where('enrollments.status', 'active')
            ->where('enrollments.progress_percent', '<', 100)
            ->count();

        $completedCount = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('enrollments.status', 'completed')
                  ->orWhere('enrollments.progress_percent', '>=', 100);
            })->count();

        $certCount = (clone $baseQuery)
            ->where('enrollments.progress_percent', '>=', 100)
            ->count();

        // Comparison calculations for resolved period
        $currentPeriodTotal = (clone $baseQuery)
            ->whereBetween('enrollments.enrolled_at', [$period['current_from'], $period['current_to']])
            ->count();

        $previousPeriodTotal = (clone $baseQuery)
            ->whereBetween('enrollments.enrolled_at', [$period['previous_from'], $period['previous_to']])
            ->count();

        $currentPeriodActive = (clone $baseQuery)
            ->where('enrollments.status', 'active')
            ->where('enrollments.progress_percent', '<', 100)
            ->whereBetween('enrollments.enrolled_at', [$period['current_from'], $period['current_to']])
            ->count();

        $previousPeriodActive = (clone $baseQuery)
            ->where('enrollments.status', 'active')
            ->where('enrollments.progress_percent', '<', 100)
            ->whereBetween('enrollments.enrolled_at', [$period['previous_from'], $period['previous_to']])
            ->count();

        $currentPeriodCompleted = (clone $baseQuery)
            ->where('enrollments.progress_percent', '>=', 100)
            ->whereBetween('enrollments.enrolled_at', [$period['current_from'], $period['current_to']])
            ->count();

        $previousPeriodCompleted = (clone $baseQuery)
            ->where('enrollments.progress_percent', '>=', 100)
            ->whereBetween('enrollments.enrolled_at', [$period['previous_from'], $period['previous_to']])
            ->count();

        $totalPercent = $this->calculatePercentChange($currentPeriodTotal, $previousPeriodTotal);
        $activePercent = $this->calculatePercentChange($currentPeriodActive, $previousPeriodActive);
        $completedPercent = $this->calculatePercentChange($currentPeriodCompleted, $previousPeriodCompleted);

        return [
            'total_enrollments' => $totalEnrollments,
            'learning_count' => $activeCount,
            'completed_count' => $completedCount,
            'certificates_count' => $certCount,
            'comparison' => [
                'total_enrollments_percent' => $totalPercent,
                'active_students_percent' => $activePercent,
                'completed_students_percent' => $completedPercent,
                'label' => $period['comparison_label'],
                'current_total' => $currentPeriodTotal,
                'previous_total' => $previousPeriodTotal,
                'current_active' => $currentPeriodActive,
                'previous_active' => $previousPeriodActive,
                'current_completed' => $currentPeriodCompleted,
                'previous_completed' => $previousPeriodCompleted,
            ],
            'period' => [
                'preset' => $period['preset'],
                'from' => $period['current_from']->format('Y-m-d'),
                'to' => $period['current_to']->format('Y-m-d'),
                'previous_from' => $period['previous_from']->format('Y-m-d'),
                'previous_to' => $period['previous_to']->format('Y-m-d'),
                'label' => $period['comparison_label'],
            ]
        ];
    }

    public function getLearnersChart(int $instructorId, array $filters = []): array
    {
        $period = $this->resolvePeriod($filters);
        $startDate = $period['current_from'];
        $endDate = $period['current_to'];
        $granularity = $period['granularity'];

        if ($granularity === 'month') {
            $records = DB::table('enrollments')
                ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                ->where('courses.instructor_id', $instructorId)
                
                ->whereIn('enrollments.status', ['active', 'completed'])
                ->whereBetween('enrollments.enrolled_at', [$startDate, $endDate])
                ->selectRaw("DATE_FORMAT(enrollments.enrolled_at, '%Y-%m') as date, COUNT(*) as total_count, SUM(CASE WHEN enrollments.status = 'active' AND enrollments.progress_percent < 100 THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN enrollments.status = 'completed' OR enrollments.progress_percent >= 100 THEN 1 ELSE 0 END) as completed_count")
                ->groupByRaw("DATE_FORMAT(enrollments.enrolled_at, '%Y-%m')")
                ->get()
                ->keyBy('date');

            $points = [];
            $current = (clone $startDate)->startOfMonth();
            while ($current->lte($endDate)) {
                $dateStr = $current->format('Y-m');
                $record = $records->get($dateStr);

                $points[] = [
                    'date' => $dateStr,
                    'enrollments' => $record ? (int) $record->total_count : 0,
                    'active' => $record ? (int) $record->active_count : 0,
                    'completed' => $record ? (int) $record->completed_count : 0,
                ];
                $current->addMonth();
            }
        } else {
            $records = DB::table('enrollments')
                ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                ->where('courses.instructor_id', $instructorId)
                
                ->whereIn('enrollments.status', ['active', 'completed'])
                ->whereBetween('enrollments.enrolled_at', [$startDate, $endDate])
                ->selectRaw("DATE(enrollments.enrolled_at) as date, COUNT(*) as total_count, SUM(CASE WHEN enrollments.status = 'active' AND enrollments.progress_percent < 100 THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN enrollments.status = 'completed' OR enrollments.progress_percent >= 100 THEN 1 ELSE 0 END) as completed_count")
                ->groupByRaw('DATE(enrollments.enrolled_at)')
                ->get()
                ->keyBy('date');

            $points = [];
            $daysCount = $period['days_count'];
            for ($i = 0; $i < $daysCount; $i++) {
                $dateStr = (clone $startDate)->addDays($i)->format('Y-m-d');
                if ((clone $startDate)->addDays($i)->gt($endDate)) break;
                $record = $records->get($dateStr);

                $points[] = [
                    'date' => $dateStr,
                    'enrollments' => $record ? (int) $record->total_count : 0,
                    'active' => $record ? (int) $record->active_count : 0,
                    'completed' => $record ? (int) $record->completed_count : 0,
                ];
            }
        }

        return [
            'granularity' => $granularity,
            'period_days' => $period['days_count'],
            'period' => [
                'preset' => $period['preset'],
                'from' => $period['current_from']->format('Y-m-d'),
                'to' => $period['current_to']->format('Y-m-d'),
                'label' => $period['comparison_label'],
            ],
            'points' => $points
        ];
    }

    public function getLearnerDetails(int $instructorId, int $enrollmentId): array
    {
        $enrollment = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->join('users', 'users.id', '=', 'enrollments.user_id')
            ->where('enrollments.id', $enrollmentId)
            ->where('courses.instructor_id', $instructorId)
            
            ->select([
                'enrollments.id as enrollment_id',
                'users.id as user_id',
                'users.full_name as user_name',
                'users.email as user_email',
                'users.phone as user_phone',
                'users.avatar_url as user_avatar',
                'courses.id as course_id',
                'courses.title as course_title',
                'courses.total_duration_seconds as course_duration_seconds',
                'enrollments.status',
                'enrollments.progress_percent',
                'enrollments.enrolled_at',
                'enrollments.completed_at',
                'enrollments.last_accessed_at',
            ])
            ->first();

        if (!$enrollment) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu học viên.');
        }

        // Total lessons count
        $totalLessons = DB::table('lessons')
            ->where('course_id', $enrollment->course_id)
            
            ->count();

        // Total course duration seconds
        $totalDurationSeconds = (int) ($enrollment->course_duration_seconds ?: DB::table('lessons')
            ->where('course_id', $enrollment->course_id)
            
            ->sum('video_duration_seconds'));

        // Completed lessons count
        $completedLessons = DB::table('lesson_progress')
            ->where('enrollment_id', $enrollment->enrollment_id)
            ->whereIn('lesson_id', function ($query) use ($enrollment) {
                $query->select('id')->from('lessons')->where('course_id', $enrollment->course_id);
            })
            ->where(function ($q) {
                $q->where('status', 'completed')->orWhereNotNull('completed_at');
            })
            ->count();

        // Learning duration total
        $learnedDurationSeconds = (int) DB::table('lesson_progress')
            ->where('enrollment_id', $enrollment->enrollment_id)
            ->whereIn('lesson_id', function ($query) use ($enrollment) {
                $query->select('id')->from('lessons')->where('course_id', $enrollment->course_id);
            })
            ->sum('learning_duration_seconds');

        // Roadmap by section
        $sections = DB::table('course_sections')
            ->where('course_id', $enrollment->course_id)
            
            ->orderBy('sort_order')
            ->get();

        $roadmap = [];
        foreach ($sections as $sec) {
            $secLessonIds = DB::table('lessons')
                ->where('course_section_id', $sec->id)
                
                ->pluck('id');

            $secTotal = count($secLessonIds);
            $secCompleted = 0;
            $lastActive = null;

            if ($secTotal > 0) {
                $progressRows = DB::table('lesson_progress')
                    ->where('enrollment_id', $enrollment->enrollment_id)
                    ->whereIn('lesson_id', $secLessonIds)
                    ->get();

                foreach ($progressRows as $pr) {
                    if ($pr->status === 'completed' || $pr->completed_at !== null) {
                        $secCompleted++;
                    }
                    if ($pr->last_accessed_at && (!$lastActive || $pr->last_accessed_at > $lastActive)) {
                        $lastActive = $pr->last_accessed_at;
                    }
                }
            }

            $status = 'not_started';
            if ($secCompleted >= $secTotal && $secTotal > 0) {
                $status = 'completed';
            } elseif ($secCompleted > 0) {
                $status = 'learning';
            }

            $roadmap[] = [
                'section_id' => $sec->id,
                'title' => $sec->title,
                'sort_order' => $sec->sort_order,
                'completed_lessons' => $secCompleted,
                'total_lessons' => $secTotal,
                'progress' => $secTotal > 0 ? round(($secCompleted / $secTotal) * 100) : 0,
                'status' => $status,
                'lastActive' => $lastActive
            ];
        }

        // Lessons Progress List
        $lessonsList = DB::table('lessons')
            ->leftJoin('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->leftJoin('lesson_progress', function ($join) use ($enrollment) {
                $join->on('lesson_progress.lesson_id', '=', 'lessons.id')
                     ->where('lesson_progress.enrollment_id', '=', $enrollment->enrollment_id);
            })
            ->where('lessons.course_id', $enrollment->course_id)
            
            ->select([
                'lessons.id as lesson_id',
                'lessons.title as lesson_title',
                'lessons.lesson_type',
                'lessons.video_duration_seconds',
                'course_sections.title as section_title',
                'lesson_progress.status as progress_status',
                'lesson_progress.completed_at',
                'lesson_progress.last_accessed_at'
            ])
            ->orderBy('course_sections.sort_order')
            ->orderBy('lessons.sort_order')
            ->get();

        $mappedLessons = [];
        foreach ($lessonsList as $les) {
            $status = 'not_started';
            if ($les->progress_status === 'completed' || $les->completed_at) {
                $status = 'completed';
            } elseif ($les->progress_status === 'learning' || $les->last_accessed_at) {
                $status = 'learning';
            }

            $mappedLessons[] = [
                'id' => $les->lesson_id,
                'title' => $les->lesson_title,
                'section_title' => $les->section_title ?: 'Tổng quan',
                'type' => $les->lesson_type ?: 'video',
                'duration' => $this->formatSecondsToHuman((int) $les->video_duration_seconds),
                'status' => $status,
                'completed_at' => $les->completed_at,
            ];
        }

        // Real Activity Log
        $activities = [];
        if ($enrollment->enrolled_at) {
            $activities[] = [
                'id' => 'act-enrolled',
                'title' => 'Đã ghi danh khóa học',
                'desc' => "Học viên đăng ký tham gia khóa học \"{$enrollment->course_title}\".",
                'time' => $enrollment->enrolled_at,
                'type' => 'enrollment'
            ];
        }

        $progressActivities = DB::table('lesson_progress')
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->where('lesson_progress.enrollment_id', $enrollment->enrollment_id)
            ->where('lessons.course_id', $enrollment->course_id)
            ->select([
                'lesson_progress.id',
                'lessons.title as lesson_title',
                'lessons.lesson_type',
                'lesson_progress.status',
                'lesson_progress.updated_at',
                'lesson_progress.completed_at',
                'lesson_progress.last_accessed_at'
            ])
            ->orderByDesc('lesson_progress.updated_at')
            ->limit(10)
            ->get();

        foreach ($progressActivities as $pa) {
            $isComp = $pa->status === 'completed' || $pa->completed_at;
            $activities[] = [
                'id' => 'act-prog-' . $pa->id,
                'title' => $isComp ? "Đã hoàn thành bài học: {$pa->lesson_title}" : "Đang học bài: {$pa->lesson_title}",
                'desc' => $isComp ? "Đã hoàn thành nội dung bài giảng." : "Học viên đang học dở bài giảng.",
                'time' => $pa->completed_at ?: ($pa->last_accessed_at ?: $pa->updated_at),
                'type' => $pa->lesson_type ?: 'video'
            ];
        }

        if ($enrollment->progress_percent >= 100 || $enrollment->completed_at) {
            $activities[] = [
                'id' => 'act-completed',
                'title' => 'Đã nhận chứng chỉ hoàn thành',
                'desc' => "Học viên đã hoàn thành 100% nội dung khóa học \"{$enrollment->course_title}\".",
                'time' => $enrollment->completed_at ?: ($enrollment->last_accessed_at ?: now()->toDateTimeString()),
                'type' => 'cert'
            ];
        }

        usort($activities, fn($a, $b) => strcmp($b['time'], $a['time']));

        return [
            'user' => [
                'id' => $enrollment->user_id,
                'name' => $enrollment->user_name,
                'email' => $enrollment->user_email,
                'phone' => $enrollment->user_phone ?: 'Chưa cập nhật SĐT',
                'avatar' => $enrollment->user_avatar ?: "https://ui-avatars.com/api/?name=" . urlencode($enrollment->user_name) . "&background=007A64&color=fff&bold=true",
            ],
            'course' => [
                'id' => $enrollment->course_id,
                'title' => $enrollment->course_title,
                'total_lessons' => $totalLessons,
                'total_duration_seconds' => $totalDurationSeconds,
            ],
            'enrollment' => [
                'id' => $enrollment->enrollment_id,
                'enrolled_at' => $enrollment->enrolled_at,
                'status' => ($enrollment->status === 'completed' || $enrollment->progress_percent >= 100) ? 'completed' : 'learning',
                'progress' => (int) $enrollment->progress_percent,
                'last_accessed_at' => $enrollment->last_accessed_at ?: $enrollment->enrolled_at,
                'lessons_completed' => $completedLessons,
                'total_lessons' => $totalLessons,
                'learning_duration' => $this->formatSecondsToHuman($learnedDurationSeconds),
                'course_duration' => $this->formatSecondsToHuman($totalDurationSeconds),
                'enrollment_code' => 'MH-' . str_pad($enrollment->enrollment_id, 8, '0', STR_PAD_LEFT),
                'learning_mode' => 'Online',
            ],
            'roadmap' => $roadmap,
            'lessons' => $mappedLessons,
            'activities' => $activities,
        ];
    }

    private function formatSecondsToHuman(int $seconds): string
    {
        if ($seconds <= 0) return '0m';
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds % 3600) / 60);
        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }
        return "{$mins}m";
    }

    private function calculatePercentChange(int $current, int $previous): float
    {
        if ($current === 0 && $previous === 0) {
            return 0.0;
        }
        if ($previous === 0) {
            return 100.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function exportLearners(int $instructorId, array $filters = []): \Illuminate\Support\Collection
    {
        $query = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->join('users', 'users.id', '=', 'enrollments.user_id')
            ->where('courses.instructor_id', $instructorId)
            
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

        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all') {
            $query->where('enrollments.course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'learning') {
                $query->where(function ($q) {
                    $q->where('enrollments.status', 'active')
                      ->where('enrollments.progress_percent', '<', 100);
                });
            } elseif ($filters['status'] === 'completed') {
                $query->where(function ($q) {
                    $q->where('enrollments.status', 'completed')
                      ->orWhere('enrollments.progress_percent', '>=', 100);
                });
            } else {
                $query->where('enrollments.status', $filters['status']);
            }
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search): void {
                $q->where('users.full_name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('courses.title', 'like', $search);
            });
        }

        if (!empty($filters['preset']) || !empty($filters['date_from']) || !empty($filters['enrolled_from'])) {
            $period = $this->resolvePeriod($filters);
            $query->whereBetween('enrollments.enrolled_at', [$period['current_from'], $period['current_to']]);
        }

        return $query->orderByDesc('enrollments.enrolled_at')->get();
    }
}