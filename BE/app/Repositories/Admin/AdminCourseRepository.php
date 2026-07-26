<?php

namespace App\Repositories\Admin;

use App\Models\Course;

final class AdminCourseRepository
{
    public function paginate(array $filters)
    {
        $q = Course::query()->with(['instructor', 'category'])->latest();
        if (!empty($filters['q'])) {
            $q->where('title', 'like', '%' . $filters['q'] . '%');
        }
        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }
        if (!empty($filters['category_id'])) {
            $q->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['instructor_id'])) {
            $q->where('instructor_id', $filters['instructor_id']);
        }
        return $q->paginate($filters['per_page'] ?? 15);
    }
}
