<?php

namespace App\Services\Course;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseViewService
{
    /**
     * Anti-duplicate window in minutes.
     */
    public const DUPLICATE_WINDOW_MINUTES = 30;

    /**
     * Record a course view (no-op in DB FINAL schema as course_views table was removed).
     */
    public function recordView(Course $course, ?User $user = null, ?Request $request = null): bool
    {
        return true;
    }
}

