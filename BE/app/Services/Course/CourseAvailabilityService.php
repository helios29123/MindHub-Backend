<?php

namespace App\Services\Course;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;

class CourseAvailabilityService
{
    public function assertCourseIsPurchasable(int $courseId): object
    {
        $course = DB::table('courses')
            ->where('id', $courseId)
            ->first();

        if (! $course) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }

        if (($course->status ?? null) !== 'published') {
            throw new BusinessException('Khóa học hiện không khả dụng để mua.', 403);
        }

        $instructor = DB::table('users')
            ->where('id', $course->instructor_id)
            ->first();

        if (! $instructor) {
            throw new BusinessException('Khóa học hiện không khả dụng để mua.', 403);
        }

        if (($instructor->status ?? null) !== 'active') {
            throw new BusinessException('Khóa học hiện không khả dụng để mua.', 403);
        }

        if ((int) ($instructor->locked ?? 0) === 1) {
            throw new BusinessException('Khóa học hiện không khả dụng để mua.', 403);
        }

        return $course;
    }

    public function instructorIsActiveAndUnlocked(int $instructorId): bool
    {
        $instructor = DB::table('users')
            ->where('id', $instructorId)
            ->first();

        return $instructor
            && ($instructor->status ?? null) === 'active'
            && (int) ($instructor->locked ?? 0) === 0;
    }
}

