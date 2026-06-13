<?php
namespace App\Policies;
use App\Models\Lesson;
use App\Models\User;
final class LessonPolicy
{
    public function view(User $user, Lesson $lesson): bool
    {
        return $this->ownsLesson($user, $lesson);
    }
    public function update(User $user, Lesson $lesson): bool
    {
        return $this->ownsLesson($user, $lesson);
    }
    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->ownsLesson($user, $lesson);
    }
    private function ownsLesson(User $user, Lesson $lesson): bool
    {
        return $lesson->course !== null
            && (int) $lesson->course->instructor_id === (int) $user->id;
    }

    public function canAccessLesson(?User $user, Lesson $lesson): bool
    {
        $course = $lesson->course;
        if (!$course || $lesson->status !== 'published' || $course->status !== 'published') {
            return false;
        }

        if ($lesson->is_preview) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'instructor' && (int) $course->instructor_id === (int) $user->id) {
            return true;
        }

        return \App\Models\Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->exists();
    }
}
