<?php

namespace App\Services\Admin;

use App\Exceptions\BusinessException;
use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminService
{
    public function getBanners(array $queryParams): array
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        $now = now();

        // 1. Calculate Summary Counts (independent of page/filters)
        $total = Banner::count();
        $active = Banner::where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')
                  ->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')
                  ->orWhere('end_at', '>=', $now);
            })
            ->count();

        $scheduled = Banner::where('status', 'active')
            ->whereNotNull('start_at')
            ->where('start_at', '>', $now)
            ->count();

        $expired = Banner::where('status', 'active')
            ->whereNotNull('end_at')
            ->where('end_at', '<', $now)
            ->count();

        $inactive = Banner::where('status', 'inactive')->count();

        $summary = [
            'total_banners' => $total,
            'active_count' => $active,
            'scheduled_count' => $scheduled,
            'expired_count' => $expired,
            'inactive_count' => $inactive,
        ];

        // 2. Query for actual filtered listing
        $query = Banner::query();

        if (!empty($queryParams['search'])) {
            $search = trim($queryParams['search']);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('target_url', 'like', "%{$search}%");
            });
        }

        if (!empty($queryParams['position']) && $queryParams['position'] !== 'all') {
            $position = $queryParams['position'];
            if ($position === 'home_top') {
                // Map both home_top and default 'home' into home_top (Đầu trang chủ)
                $query->whereIn('position', ['home_top', 'home']);
            } else {
                $query->where('position', $position);
            }
        }

        if (!empty($queryParams['status']) && $queryParams['status'] !== 'all') {
            $query->where('status', $queryParams['status']);
        }

        if (!empty($queryParams['view_mode']) && $queryParams['view_mode'] !== 'all') {
            switch ($queryParams['view_mode']) {
                case 'active':
                    $query->where('status', 'active')
                          ->where(function ($q) use ($now) {
                              $q->whereNull('start_at')
                                ->orWhere('start_at', '<=', $now);
                          })
                          ->where(function ($q) use ($now) {
                              $q->whereNull('end_at')
                                ->orWhere('end_at', '>=', $now);
                          });
                    break;
                case 'scheduled':
                    $query->where('status', 'active')
                          ->whereNotNull('start_at')
                          ->where('start_at', '>', $now);
                    break;
                case 'expired':
                    $query->where('status', 'active')
                          ->whereNotNull('end_at')
                          ->where('end_at', '<', $now);
                    break;
                case 'inactive':
                    $query->where('status', 'inactive');
                    break;
            }
        }

        $paginator = $query->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($queryParams);

        return [
            'paginator' => $paginator,
            'summary' => $summary,
        ];
    }

    public function getBanner(int $id): Banner
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        return $banner;
    }

    public function createBanner(array $data): Banner
    {
        return Banner::create($data);
    }

    public function updateBanner(int $id, array $data): Banner
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $banner->update($data);
        return $banner;
    }

    public function deleteBanner(int $id): void
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $banner->delete();
    }

    public function getCategories(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        $query = \App\Models\Category::query()->with(['parent', 'children']);

        if (!empty($queryParams['search'])) {
            $search = trim((string) $queryParams['search']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (!empty($queryParams['status'])) {
            $query->where('status', $queryParams['status']);
        }

        if (array_key_exists('parent_id', $queryParams)) {
            $query->where('parent_id', $queryParams['parent_id']);
        }

        $sortBy = $queryParams['sort_by'] ?? 'sort_order';
        $sortDirection = $queryParams['sort_direction'] ?? 'asc';

        return $query->orderBy($sortBy, $sortDirection)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getCategory(int $id): \App\Models\Category
    {
        $category = \App\Models\Category::with(['parent', 'children'])->find($id);

        if (!$category) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        return $category;
    }

    public function createCategory(array $data): \App\Models\Category
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data): \App\Models\Category {
            if (!isset($data['sort_order']) || $data['sort_order'] === null) {
                $maxSortOrder = \App\Models\Category::where('parent_id', $data['parent_id'] ?? null)->max('sort_order');
                $data['sort_order'] = ((int) $maxSortOrder) + 1;
            }

            $data['status'] ??= 'active';

            return \App\Models\Category::create($data)->load(['parent', 'children']);
        });
    }

    public function updateCategory(int $id, array $data): \App\Models\Category
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $data): \App\Models\Category {
            $category = \App\Models\Category::find($id);

            if (!$category) {
                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
            }

            if (array_key_exists('parent_id', $data) && $data['parent_id'] !== $category->parent_id) {
                if ((int) $data['parent_id'] === $category->id) {
                    throw new BusinessException('Danh mục cha không hợp lệ.', 422);
                }

                // Check cyclic dependency
                $currentParentId = $data['parent_id'];
                while ($currentParentId !== null) {
                    if ((int) $currentParentId === $category->id) {
                        throw new BusinessException('Không thể tạo vòng lặp danh mục.', 422);
                    }
                    $parent = \App\Models\Category::find($currentParentId);
                    $currentParentId = $parent ? $parent->parent_id : null;
                }
            }

            $allowedFields = ['name', 'slug', 'parent_id', 'description', 'sort_order', 'status'];
            $updateData = [];
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            $category->update($updateData);

            return $category->refresh()->load(['parent', 'children']);
        });
    }

    public function deleteCategory(int $id): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($id): void {
            $category = \App\Models\Category::with('children', 'courses')->find($id);

            if (!$category) {
                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
            }

            if ($category->children->count() > 0) {
                throw new BusinessException('Không thể xóa danh mục đang có danh mục con.', 400);
            }

            if ($category->courses->count() > 0) {
                throw new BusinessException('Không thể xóa danh mục đang có khóa học liên kết.', 400);
            }

            $category->delete();
        });
    }

    public function getCourses(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 10000);
        $query = \App\Models\Course::query()
            ->with(['instructor', 'categories'])
            ->withCount([
                'enrollments',
                'sections',
                'lessons',
                'reviews as review_count',
                'comments as comment_count' => fn($q) => $q->where('comments.status', 'visible'),
                'orders as paid_order_count' => fn($q) => $q->where('orders.status', 'paid'),
            ])
            ->withSum([
                'orders as gross_revenue' => fn($q) => $q->where('orders.status', 'paid')
            ], 'amount')
            ->withAvg('reviews as average_rating', 'rating');

        if (!empty($queryParams['search'])) {
            $search = trim((string) $queryParams['search']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($queryParams['status'])) {
            $query->where('status', $queryParams['status']);
        }

        if (!empty($queryParams['instructor_id'])) {
            $query->where('instructor_id', $queryParams['instructor_id']);
        }

        if (!empty($queryParams['category_id'])) {
            $query->whereHas('categories', function ($builder) use ($queryParams): void {
                $builder->where('categories.id', $queryParams['category_id']);
            });
        }

        if (!empty($queryParams['level'])) {
            $query->where('level', $queryParams['level']);
        }

        $sortBy = $queryParams['sort_by'] ?? 'created_at';
        $sortDirection = $queryParams['sort_direction'] ?? 'desc';

        if ($sortBy === 'enrollment_count') {
            $query->withCount('enrollments')
                  ->orderBy('enrollments_count', $sortDirection);
        } elseif ($sortBy === 'gross_revenue') {
            $query->select('courses.*')
                  ->leftJoin('orders', function ($join) {
                      $join->on('courses.id', '=', 'orders.course_id')
                           ->where('orders.status', '=', 'paid');
                  })
                  ->selectRaw('COALESCE(SUM(orders.amount), 0) as calculated_revenue')
                  ->groupBy('courses.id')
                  ->orderBy('calculated_revenue', $sortDirection);
        } elseif ($sortBy === 'average_rating') {
            $query->select('courses.*')
                  ->leftJoin('orders', 'courses.id', '=', 'orders.course_id')
                  ->leftJoin('course_reviews', 'orders.id', '=', 'course_reviews.order_id')
                  ->selectRaw('COALESCE(AVG(course_reviews.rating), 0) as calculated_rating')
                  ->groupBy('courses.id')
                  ->orderBy('calculated_rating', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query->orderBy('courses.id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getCoursesSummary(): array
    {
        $statusCounts = \App\Models\Course::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalCourses = array_sum($statusCounts);
        $publishedCourses = $statusCounts['published'] ?? 0;
        $pendingCourses = $statusCounts['pending_review'] ?? 0;
        $draftCourses = $statusCounts['draft'] ?? 0;
        $hiddenCourses = $statusCounts['hidden'] ?? 0;
        $rejectedCourses = $statusCounts['rejected'] ?? 0;
        
        $newCourses = \App\Models\Course::where('status', 'published')
            ->where('published_at', '>=', '2026-06-30 00:00:00')
            ->count();

        $totalEnrollments = \App\Models\Enrollment::count();

        $paidOrders = \App\Models\Order::where('status', 'paid');
        $totalPaidOrders = $paidOrders->count();
        $totalGrossRevenue = (float) $paidOrders->sum('amount');

        $averageRating = (float) (\App\Models\CourseReview::count() > 0 
            ? round(\App\Models\CourseReview::avg('rating'), 1) 
            : 0.0);

        return [
            'total_courses' => $totalCourses,
            'published_courses' => $publishedCourses,
            'pending_review_courses' => $pendingCourses,
            'draft_courses' => $draftCourses,
            'hidden_courses' => $hiddenCourses,
            'rejected_courses' => $rejectedCourses,
            'new_courses_30_days' => $newCourses,
            'total_enrollments' => $totalEnrollments,
            'total_paid_orders' => $totalPaidOrders,
            'total_gross_revenue' => $totalGrossRevenue,
            'average_rating' => $averageRating,
        ];
    }

    public function getCourse(int $id): \App\Models\Course
    {
        $course = \App\Models\Course::with(['instructor', 'categories', 'sections.lessons'])->find($id);

        if (!$course) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        return $course;
    }

    public function updateCourse(int $id, array $data): \App\Models\Course
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $data): \App\Models\Course {
            $course = \App\Models\Course::find($id);

            if (!$course) {
                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
            }

            $effectivePrice = array_key_exists("price", $data) ? $data["price"] : $course->price;
            $effectiveSalePrice = array_key_exists("sale_price", $data) ? $data["sale_price"] : $course->sale_price;

            if ($effectiveSalePrice !== null && (float) $effectiveSalePrice > (float) $effectivePrice) {
                throw new BusinessException("Giá khuyến mãi không được lớn hơn giá gốc.", 422);
            }

            $allowedFields = [
                'status',
                'is_featured',
                'admin_reject_reason',
                'title',
                'slug',
                'short_description',
                'description',
                'thumbnail_url',
                'intro_video_url',
                'price',
                'sale_price',
                'level',
                'language',
                'requirements',
                'outcomes'
            ];

            $updateData = [];
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            $oldStatus = $course->status;

            if (isset($updateData['status']) && $updateData['status'] === 'published' && $course->published_at === null) {
                $updateData['published_at'] = now();
            }

            $course->update($updateData);

            $refreshedCourse = $course->refresh()->load(['instructor', 'categories']);

            if (isset($updateData['status']) && in_array($updateData['status'], ['published', 'approved'], true) && $oldStatus !== $updateData['status']) {
                try {
                    if ($refreshedCourse->instructor && !empty($refreshedCourse->instructor->email)) {
                        \Illuminate\Support\Facades\Mail::to($refreshedCourse->instructor->email)->send(
                            new \App\Mail\CourseApprovedNotificationMail($refreshedCourse->instructor, $refreshedCourse)
                        );

                        \App\Models\Notification::create([
                            'user_id' => $refreshedCourse->instructor->id,
                            'type' => 'course_approved',
                            'title' => '🎉 Khóa học của bạn đã được duyệt',
                            'message' => "Khóa học \"{$refreshedCourse->title}\" đã được phê duyệt và xuất bản công khai.",
                            'action_url' => "/courses/" . ($refreshedCourse->slug ?: $refreshedCourse->id),
                            'channel' => 'database',
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to send admin update course approval notification: ' . $e->getMessage());
                }
            }

            return $refreshedCourse;
        });
    }

    public function getUsers(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 15), 100);
        $query = \App\Models\User::query();

        if (!empty($queryParams['search'])) {
            $search = trim((string) $queryParams['search']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($queryParams['role'])) {
            $query->where('role', $queryParams['role']);
        }

        if (!empty($queryParams['status'])) {
            $query->where('status', $queryParams['status']);
        }

        $sortBy = $queryParams['sort_by'] ?? 'created_at';
        $sortDirection = $queryParams['sort_direction'] ?? 'desc';

        return $query->orderBy($sortBy, $sortDirection)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getUsersReport(array $queryParams): array
    {
        $baseQuery = \App\Models\User::query();

        $totalUsers = (clone $baseQuery)->count();
        $totalLearners = (clone $baseQuery)->where('role', 'learner')->count();
        $totalInstructors = (clone $baseQuery)->where('role', 'instructor')->count();
        $activeUsers = (clone $baseQuery)->where('status', 'active')->where('locked', false)->count();
        $inactiveUsers = (clone $baseQuery)->where('status', 'inactive')->where('locked', false)->count();
        $lockedUsers = (clone $baseQuery)->where(function ($q) {
            $q->where('locked', true)->orWhere('status', 'locked');
        })->count();
        $unverifiedUsers = (clone $baseQuery)->whereNull('email_verified_at')->count();
        $noLoginUsers = (clone $baseQuery)->whereNull('last_login_at')->count();

        $dateFrom = $queryParams['date_from'] ?? null;
        $dateTo = $queryParams['date_to'] ?? null;
        
        $newUsersQuery = clone $baseQuery;
        if ($dateFrom) {
            $newUsersQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $newUsersQuery->whereDate('created_at', '<=', $dateTo);
        }
        if (!$dateFrom && !$dateTo) {
            $newUsersQuery->where('created_at', '>=', now()->subDays(7));
        }
        $newUsersInPeriod = $newUsersQuery->count();

        $summary = [
            'total_users' => $totalUsers,
            'total_learners' => $totalLearners,
            'total_instructors' => $totalInstructors,
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'locked_users' => $lockedUsers,
            'unverified_users' => $unverifiedUsers,
            'no_login_users' => $noLoginUsers,
            'new_users_in_period' => $newUsersInPeriod,
        ];

        $query = \App\Models\User::query();

        if (!empty($queryParams['search'])) {
            $search = trim((string) $queryParams['search']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($queryParams['role'])) {
            $query->where('role', $queryParams['role']);
        }

        if (!empty($queryParams['status'])) {
            $statusVal = $queryParams['status'];
            if ($statusVal === 'locked') {
                $query->where(function ($q) {
                    $q->where('locked', true)->orWhere('status', 'locked');
                });
            } elseif ($statusVal === 'active') {
                $query->where('status', 'active')->where('locked', false);
            } elseif ($statusVal === 'inactive') {
                $query->where('status', 'inactive')->where('locked', false);
            } else {
                $query->where('status', $statusVal);
            }
        }

        if (!empty($queryParams['email_verified'])) {
            if ($queryParams['email_verified'] === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($queryParams['email_verified'] === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        if (isset($queryParams['no_login']) && ($queryParams['no_login'] === 'true' || $queryParams['no_login'] === '1' || $queryParams['no_login'] === 1)) {
            $query->whereNull('last_login_at');
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $sortBy = $queryParams['sort_by'] ?? 'newest';
        $sortDirection = $queryParams['sort_direction'] ?? null;

        if ($sortBy === 'newest') {
            $sortColumn = 'created_at';
            $sortDirection ??= 'desc';
        } elseif ($sortBy === 'oldest') {
            $sortColumn = 'created_at';
            $sortDirection ??= 'asc';
        } elseif ($sortBy === 'name_asc') {
            $sortColumn = 'full_name';
            $sortDirection ??= 'asc';
        } elseif ($sortBy === 'name_desc') {
            $sortColumn = 'full_name';
            $sortDirection ??= 'desc';
        } elseif ($sortBy === 'last_login') {
            $sortColumn = 'last_login_at';
            $sortDirection ??= 'desc';
        } else {
            $sortColumn = $sortBy;
            $sortDirection ??= 'desc';
        }

        $perPage = min((int) ($queryParams['per_page'] ?? 15), 100);
        $paginator = $query->orderBy($sortColumn, $sortDirection)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);

        return [
            'summary' => $summary,
            'paginator' => $paginator,
        ];
    }

    public function getUser(int $id): \App\Models\User
    {
        $user = \App\Models\User::where('id', $id)->first();

        if (!$user) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        return $user;
    }

    public function createUser(array $data): \App\Models\User
    {
        $data['password_hash'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        unset($data['password']);

        $data['status'] = $data['status'] ?? 'active';
        $data['locked'] = $data['status'] === 'locked';

        return \App\Models\User::create($data);
    }

    public function updateUser(int $id, array $data, int $currentAdminId): \App\Models\User
    {
        $user = $this->getUser($id);

        if (empty($data)) {
            throw new BusinessException('Cần ít nhất một trường hợp lệ để cập nhật.', 422);
        }

        if ($user->id === $currentAdminId) {
            if (isset($data['role']) && $data['role'] !== $user->role) {
                throw new BusinessException('Không được tự đổi role của chính mình.', 422);
            }
            if (isset($data['status']) && $data['status'] !== 'active') {
                throw new BusinessException('Không được tự khóa hoặc vô hiệu hóa chính mình.', 422);
            }
        }

        if (isset($data['password'])) {
            $data['password_hash'] = \Illuminate\Support\Facades\Hash::make($data['password']);
            unset($data['password']);
        }

        if (isset($data['status'])) {
            $data['locked'] = $data['status'] === 'locked';
        }

        $allowedFields = ['full_name', 'email', 'password_hash', 'phone', 'role', 'status', 'locked', 'locked_reason'];
        $updateData = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            throw new BusinessException('Cần ít nhất một trường hợp lệ để cập nhật.', 422);
        }

        $user->update($updateData);

        return $user->refresh();
    }

    public function deleteUser(int $id, int $currentAdminId): void
    {
        $user = $this->getUser($id);

        if ($user->id === $currentAdminId) {
            throw new BusinessException('Không được tự xóa chính mình.', 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($user): void {
            $user->delete();
            
            try {
                \Illuminate\Support\Facades\DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->update(['revoked_at' => now()]);
            } catch (\Exception $e) {
                info('Session revoke error: ' . $e->getMessage());
            }
        });
    }
}
