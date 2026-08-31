<?php

namespace App\Services\Report;

use App\Exceptions\BusinessException;
use App\Repositories\Report\LearnerRiskRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
            throw new BusinessException('Không tìm thấy khóa học hoặc bạn không có quyền truy cập.', 404);
        }

        $ageDays = (int) config('report.learner_risk_enrollment_age_days', 14);
        $progressThreshold = (float) config('report.learner_risk_progress_threshold', 30.0);
        $inactiveThreshold = (int) ($filters['inactive_days'] ?? config('report.learner_risk_inactive_days', 7));

        $enrollments = $this->repository->getEligibleEnrollmentsForRisk($courseId, $ageDays, $progressThreshold);
        $enrollmentIds = $enrollments->pluck('id')->all();
        $activityMap = $this->repository->getLatestActivityMap($enrollmentIds);

        $now = Carbon::now();
        $inactiveCutoff = $now->copy()->subDays($inactiveThreshold);
        $results = new Collection();

        foreach ($enrollments as $enrollment) {
            $user = $enrollment->user;
            if (!$user) {
                continue;
            }

            $lastActivityStr = $activityMap->get($enrollment->id);
            $lastActivity = $lastActivityStr ? Carbon::parse($lastActivityStr) : null;

            // Condition 3: No actual learning activity for at least $inactiveThreshold days (default 7 days)
            // If learner never studied, inactivity age is their enrollment date (which is already >= 14 days >= 7 days)
            $isInactive = false;
            $daysSinceLastActivity = 0;

            if ($lastActivity) {
                $daysSinceLastActivity = (int) $lastActivity->diffInDays($now);
                $isInactive = $lastActivity->lte($inactiveCutoff);
            } else {
                $enrolledDate = Carbon::parse($enrollment->enrolled_at ?? $enrollment->created_at);
                $daysSinceLastActivity = (int) $enrolledDate->diffInDays($now);
                $isInactive = $enrolledDate->lte($inactiveCutoff);
            }

            if (!$isInactive) {
                continue;
            }

            // All 4 conditions met:
            // 1. Enrollment age >= 14 days (Filtered in repository)
            // 2. Progress < 30% (Filtered in repository)
            // 3. Inactive >= 7 days (Verified above)
            // 4. Trial excluded (Filtered in repository)

            $reasons = [
                "Ghi danh trên {$ageDays} ngày nhưng tiến độ dưới {$progressThreshold}%",
                "Không có hoạt động học tập trong {$daysSinceLastActivity} ngày gần đây",
            ];

            // Calculate Risk Score & Level
            $score = 50;
            if ($enrollment->progress_percent < 10) {
                $score += 25;
            } elseif ($enrollment->progress_percent < 20) {
                $score += 15;
            }

            if ($daysSinceLastActivity >= 14) {
                $score += 25;
            } elseif ($daysSinceLastActivity >= 7) {
                $score += 15;
            }

            $level = 'medium';
            if ($score >= 75) {
                $level = 'high';
            } elseif ($score < 50) {
                $level = 'low';
            }

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
                'last_accessed_at' => $lastActivity ? $lastActivity->toIso8601String() : ($enrollment->last_accessed_at ? Carbon::parse($enrollment->last_accessed_at)->toIso8601String() : null),
                'risk_level' => $level,
                'risk_score' => min(100, $score),
                'reasons' => $reasons,
            ]);
        }

        $sortedResults = $results->sortByDesc('risk_score')->values();

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($filters['per_page'] ?? 15)));
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
