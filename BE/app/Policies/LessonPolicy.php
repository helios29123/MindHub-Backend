<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

final class LessonPolicy
{
    public function update(User $user, Lesson $lesson): bool
    {
        return $user->role === 'instructor'
            && $lesson->course !== null
            && (int) $lesson->course->instructor_id === (int) $user->id;
    }
}