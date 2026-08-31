<?php

namespace App\Repositories\Report;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LearnerRiskRepository
{
    public function getCourseForInstructor(int $courseId, int $instructorId): ?Course
    {
        return Course::where('id', $courseId)
            ->where('instructor_id', $instructorId)
            ->first();
    }

    public function getEligibleEnrollmentsForRisk(int $courseId, int $ageDays = 14, float $maxProgress = 30.0): Collection
    {
        $ageCutoff = now()->subDays($ageDays);

        return Enrollment::query()
            ->where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed'])
            ->where(function ($q) use ($ageCutoff) {
                $q->where('enrolled_at', '<=', $ageCutoff)
                  ->orWhere('created_at', '<=', $ageCutoff);
            })
            ->where('progress_percent', '<', $maxProgress)
            ->whereHas('order', function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNull('payment_method')
                       ->orWhere('payment_method', '<>', 'coupon_trial');
                });
            })
            ->with(['user'])
            ->get();
    }

    public function getLatestActivityMap(array $enrollmentIds): Collection
    {
        if (empty($enrollmentIds)) {
            return collect();
        }

        $dailyActivityMap = DB::table('learning_daily_activity')
            ->whereIn('enrollment_id', $enrollmentIds)
            ->where('video_learning_seconds', '>', 0)
            ->select('enrollment_id', DB::raw('MAX(activity_date) as latest_date'))
            ->groupBy('enrollment_id')
            ->pluck('latest_date', 'enrollment_id');

        $lessonProgressMap = DB::table('lesson_progress')
            ->whereIn('enrollment_id', $enrollmentIds)
            ->select('enrollment_id', DB::raw('MAX(COALESCE(last_accessed_at, updated_at)) as latest_date'))
            ->groupBy('enrollment_id')
            ->pluck('latest_date', 'enrollment_id');

        $result = collect();
        foreach ($enrollmentIds as $id) {
            $daDate = $dailyActivityMap->get($id);
            $lpDate = $lessonProgressMap->get($id);

            $latest = null;
            if ($daDate && $lpDate) {
                $latest = max($daDate, $lpDate);
            } elseif ($daDate) {
                $latest = $daDate;
            } elseif ($lpDate) {
                $latest = $lpDate;
            }

            $result->put($id, $latest);
        }

        return $result;
    }
}
