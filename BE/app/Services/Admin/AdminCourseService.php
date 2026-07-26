<?php

namespace App\Services\Admin;

use App\Models\Course;
use App\Models\User;
use App\Repositories\Admin\AdminCourseRepository;

final class AdminCourseService
{
    public function __construct(private readonly AdminCourseRepository $repo, private readonly AdminNotificationService $notifications) {}
    public function paginate(array $filters)
    {
        return $this->repo->paginate($filters);
    }
    public function show(Course $course): Course
    {
        return $course->load(['instructor', 'category', 'sections.lessons']);
    }
    public function approve(Course $course, User $admin): Course
    {
        $course->update(['status' => 'published', 'approved_at' => now(), 'approved_by' => $admin->id]);
        $this->notifications->audit($admin, 'course.approve', $course);
        return $course->fresh(['instructor', 'category']);
    }
    public function reject(Course $course, ?string $reason, User $admin): Course
    {
        $course->update(['status' => 'rejected', 'rejected_reason' => $reason]);
        $this->notifications->audit($admin, 'course.reject', $course, [], ['reason' => $reason]);
        return $course->fresh(['instructor', 'category']);
    }
    public function hide(Course $course, ?string $reason, User $admin): Course
    {
        $course->update(['status' => 'hidden', 'hidden_reason' => $reason]);
        $this->notifications->audit($admin, 'course.hide', $course, [], ['reason' => $reason]);
        return $course->fresh(['instructor', 'category']);
    }
    public function publish(Course $course, User $admin): Course
    {
        return $this->approve($course, $admin);
    }
    public function bulkApprove(array $ids, User $admin): array
    {
        $count = Course::query()->whereIn('id', $ids)->update(['status' => 'published', 'approved_by' => $admin->id, 'approved_at' => now()]);
        return ['updated' => $count];
    }
}
