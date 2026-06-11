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
}
