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
        return $course->load(['instructor', 'categories', 'sections.lessons']);
    }
    public function approve(Course $course, User $admin): Course
    {
        $course->update(['status' => 'published', 'published_at' => now(), 'reviewed_by' => $admin->id]);
        $this->notifications->audit($admin, 'course.approve', $course);
        return $course->fresh(['instructor', 'categories']);
    }
    public function reject(Course $course, ?string $reason, User $admin): Course
    {
        $course->update(['status' => 'rejected', 'admin_reject_reason' => $reason, 'reviewed_by' => $admin->id]);
        $this->notifications->audit($admin, 'course.reject', $course, [], ['reason' => $reason]);
        return $course->fresh(['instructor', 'categories']);
    }
    public function hide(Course $course, ?string $reason, User $admin): Course
    {
        $course->update(['status' => 'hidden']);
        $this->notifications->audit($admin, 'course.hide', $course, [], ['reason' => $reason]);
        return $course->fresh(['instructor', 'categories']);
    }
    public function publish(Course $course, User $admin): Course
    {
        return $this->approve($course, $admin);
    }
    public function bulkApprove(array $ids, User $admin): array
    {
        $count = Course::query()->whereIn('id', $ids)->update(['status' => 'published', 'reviewed_by' => $admin->id, 'published_at' => now()]);
        return ['updated' => $count];
    }
}

