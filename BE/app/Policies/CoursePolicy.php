<?php
namespace App\Policies;
use App\Models\Course;
use App\Models\User;
final class CoursePolicy
{
    public function update(User $user, Course $course): bool
    {
        return (int) $course->instructor_id === (int) $user->id;
    }
    public function delete(User $user, Course $course): bool
    {
        return (int) $course->instructor_id === (int) $user->id;
    }
    public function manageLessons(User $user, Course $course): bool
    {
        return (int) $course->instructor_id === (int) $user->id;
    }
}
