<?php

namespace App\Services\Report;

use App\Repositories\Report\LearnerRiskRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class LearnerRiskService
{
    public function __construct(
        private readonly LearnerRiskRepository $repository
    ) {
    }

    public function getLearnerRiskReport(int $instructorId, int $courseId, array $filters): LengthAwarePaginator
    {
        $course = $this->repository->getCourseForInstructor($courseId, $instructorId);

        if (!$course) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy khóa học.', 404);
        }

        if ((int) $course->instructor_id !== $instructorId) {
            throw new \App\Exceptions\BusinessException('Bạn không có quyền xem báo cáo của khóa học này.', 403);
        }

        $inactiveDaysThreshold = (int) ($filters['inactive_days'] ?? 14);
        $thresholdDate = Carbon::now()->subDays($inactiveDaysThreshold);

        $enrollments = $this->repository->getEnrollmentsForCourse($courseId);
        
        $failedQuizzesMap = $this->repository->getFailedQuizzesForCourse($courseId);
        $lessonProgressMap = $this->repository->getLessonProgressCountForCourse($courseId);

        $results = new Collection();

        foreach ($enrollments as $enrollment) {
            $user = $enrollment->user;
            if (!$user) continue;

            $score = 0;
            $reasons = [];

            // Rule 1: Lâu chưa học
            $lastAccessed = $enrollment->last_accessed_at ? Carbon::parse($enrollment->last_accessed_at) : null;
            if (!$lastAccessed || $lastAccessed->lt($thresholdDate)) {
                $score += 40;
                $reasons[] = 'Lâu chưa học';
            }

            // Rule 2: Tiến độ thấp
            if ($enrollment->progress_percent !== null && $enrollment->progress_percent < 30) {
                $score += 25;
                $reasons[] = 'Tiến độ thấp';
            }

            // Rule 3: Quiz chưa đạt
            if ($failedQuizzesMap->has($user->id)) {
                $score += 20;
                $reasons[] = 'Quiz chưa đạt';
            }

            // Rule 4: Ít hoạt động (ít lesson progress)
            $progressCount = $lessonProgressMap->get($user->id)->count ?? 0;
            if ($progressCount < 3) {
                $score += 15;
                $reasons[] = 'Ít hoạt động gần đây';
            }

            // Determine Level
            $level = 'low';
            if ($score >= 70) {
                $level = 'high';
            } elseif ($score >= 40) {
                $level = 'medium';
            }

            // Filter by level if provided
            if (!empty($filters['risk_level']) && $filters['risk_level'] !== $level) {
                continue;
            }

            $results->push([
                'learner_id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'enrollment_id' => $enrollment->id,
                'enrollment_status' => $enrollment->status,
                'progress_percent' => (float) $enrollment->progress_percent,
                'last_accessed_at' => $enrollment->last_accessed_at ? $enrollment->last_accessed_at->toIso8601String() : null,
                'risk_level' => $level,
                'risk_score' => $score,
                'reasons' => $reasons,
            ]);
        }

        // Sort by risk_score desc
        $sortedResults = $results->sortByDesc('risk_score')->values();

        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 15);
        $offset = ($page - 1) * $perPage;
        
        $paginatedItems = $sortedResults->slice($offset, $perPage)->all();

        return new LengthAwarePaginator(
            array_values($paginatedItems),
            $sortedResults->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
