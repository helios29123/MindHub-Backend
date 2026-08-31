<?php

namespace App\Services\Course;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Wishlist;
use App\Repositories\Auth\SessionRepository;
use App\Repositories\User\UserRepository;
use App\Services\Auth\AccessTokenService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CoursePublicService
{
    public function __construct(
        private readonly AccessTokenService $accessTokenService,
        private readonly SessionRepository $sessionRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    public function show(string $slug): array
    {
        // 1. Fetch the course by slug or numeric ID
        $course = Course::where(function ($query) use ($slug) {
                $query->where('slug', $slug);
                if (is_numeric($slug)) {
                    $query->orWhere('id', (int) $slug);
                }
            })
            ->where('status', 'published')
            ->first();

        if (!$course) {
            throw new ModelNotFoundException("Không tìm thấy dữ liệu.");
        }

        // 2. Resolve optional authenticated user from Bearer token
        $user = $this->resolveOptionalUser();


        // 3. Eager load relationships with status and ordering constraints
        $course->load([
            'instructor:id,full_name,avatar_url',
            'instructor.instructorProfile',
            'sections' => function ($query) {
                $query->where('status', 'published')->orderBy('sort_order');
            },
            'sections.lessons' => function ($query) {
                $query->where('status', 'published')->orderBy('sort_order');
            },
            'reviews' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'reviews.order:id,user_id,course_id',
            'reviews.order.user:id,full_name,avatar_url',
            'faqs' => function ($query) {
                $query->where('status', 'active')->orderBy('course_faqs.sort_order');
            }
        ]);

        // 4. Calculate personalized details
        $isEnrolled = false;
        $enrollmentStatus = null;
        $isInWishlist = false;
        $hasAccess = false;

        if ($user) {
            // Check enrollment
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment) {
                $isEnrolled = true;
                $enrollmentStatus = $enrollment->status;
                // If enrollment is active or completed, user has full access to content
                if (in_array($enrollment->status, ['active', 'completed'])) {
                    $hasAccess = true;
                }
            }

            // Check wishlist
            $isInWishlist = Wishlist::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->exists();

            // Check if current user is the instructor of this course
            if ((int) $course->instructor_id === (int) $user->id) {
                $hasAccess = true;
            }
        }

        return [
            'course' => $course,
            'is_enrolled' => $isEnrolled,
            'enrollment_status' => $enrollmentStatus,
            'is_in_wishlist' => $isInWishlist,
            'has_access' => $hasAccess,
        ];
    }

    public function outline(int $id): array
    {
        // 1. Fetch the course by ID
        $course = Course::where('id', $id)
            ->where('status', 'published')
            ->first();

        if (!$course) {
            throw new ModelNotFoundException("Không tìm thấy dữ liệu.");
        }

        // 2. Resolve optional authenticated user from Bearer token
        $user = $this->resolveOptionalUser();

        // 3. Eager load sections and lessons with status and ordering constraints
        $course->load([
            'sections' => function ($query) {
                $query->where('status', 'published')->orderBy('sort_order');
            },
            'sections.lessons' => function ($query) {
                $query->where('status', 'published')->orderBy('sort_order');
            }
        ]);

        // 4. Calculate if the user has full access to the outline lessons
        $hasAccess = false;
        if ($user) {
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment && in_array($enrollment->status, ['active', 'completed'])) {
                $hasAccess = true;
            }

            if ((int) $course->instructor_id === (int) $user->id) {
                $hasAccess = true;
            }
        }

        return [
            'sections' => $course->sections,
            'has_access' => $hasAccess,
        ];
    }

    public function previewLesson(int $id): \App\Models\Lesson
    {
        // 1. Fetch lesson by ID (not soft-deleted)
        $lesson = \App\Models\Lesson::with('course')->find($id);

        if (!$lesson) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        // 2. Check if course is published
        $course = $lesson->course;
        if (!$course || $course->status !== 'published') {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
        }

        // 3. Check if lesson status is hidden
        if ($lesson->status === 'hidden') {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
        }

        // 4. Check if lesson is previewable and published
        if (!$lesson->is_preview || $lesson->status !== 'published') {
            throw new \App\Exceptions\BusinessException('Bài học này không được xem trước.', 403);
        }

        return $lesson;
    }

    public function reviews(int $id, array $params): array
    {
        // 1. Fetch course by ID
        $course = Course::find($id);

        if (!$course) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        if ($course->status !== 'published') {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
        }

        // 2. Query reviews
        $query = $course->reviews()->with('order.user');

        // Apply rating filter
        if (isset($params['rating'])) {
            $query->where('rating', (int) $params['rating']);
        }

        // Apply sorting
        $sort = $params['sort'] ?? 'newest';
        if ($sort === 'newest') {
            $query->orderBy('course_reviews.created_at', 'desc');
        } elseif ($sort === 'highest_rating') {
            $query->orderBy('rating', 'desc')->orderBy('course_reviews.created_at', 'desc');
        } elseif ($sort === 'lowest_rating') {
            $query->orderBy('rating', 'asc')->orderBy('course_reviews.created_at', 'desc');
        }

        // Paginate reviews
        $perPage = (int) ($params['per_page'] ?? 10);
        $paginator = $query->paginate($perPage);

        return [
            'paginator' => $paginator,
        ];
    }

    public function showInstructor(int $id): array
    {
        // 1. Fetch user by ID
        $user = \App\Models\User::find($id);

        if (!$user) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        // 2. Check if user is an active instructor
        if ($user->role !== 'instructor' || $user->status !== 'active') {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
        }

        // 3. Eager load and calculate stats
        $instructor = \App\Models\User::query()
            ->select('users.*')
            ->where('users.id', $id)
            ->with(['instructorProfile', 'publishedCourses'])
            ->withCount([
                'publishedCourses as published_courses_count',
                'courseEnrollments as total_enrollments_count',
            ])
            ->selectSub(function ($query) {
                $query->from('course_reviews')
                    ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
                    ->join('courses', 'courses.id', '=', 'orders.course_id')
                    ->whereColumn('courses.instructor_id', 'users.id')
                    ->where('courses.status', 'published')
                    ->select(\Illuminate\Support\Facades\DB::raw('AVG(course_reviews.rating)'));
            }, 'average_rating')
            ->first();

        // 4. Resolve optional authenticated user
        $currentUser = $this->resolveOptionalUser();

        // Enforce Privacy Visibility Settings
        $userModel = $user->fresh() ?? $user;
        $rawMeta = $userModel->locked_reason;
        $meta = ($rawMeta && str_starts_with(trim((string)$rawMeta), '{')) ? json_decode((string)$rawMeta, true) : [];
        $privacy = data_get($meta, 'privacy_settings', [
            'profile_visibility' => 'public',
        ]);

        $visibility = $privacy['profile_visibility'] ?? 'public';
        $isOwner = $currentUser && (int)$currentUser->id === (int)$id;

        if (!$isOwner) {
            if ($visibility === 'private') {
                throw new \App\Exceptions\BusinessException('Hồ sơ giảng viên này đang ở chế độ riêng tư.', 403);
            }
            if ($visibility === 'students_only') {
                $isStudent = false;
                if ($currentUser) {
                    $isStudent = \Illuminate\Support\Facades\DB::table('enrollments')
                        ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                        ->where('courses.instructor_id', $user->id)
                        ->where('enrollments.user_id', $currentUser->id)
                        ->whereIn('enrollments.status', ['active', 'completed'])
                        ->exists();
                }
                if (!$isStudent) {
                    throw new \App\Exceptions\BusinessException('Hồ sơ giảng viên này chỉ dành cho học viên đã đăng ký khóa học.', 403);
                }
            }
        }

        foreach ($instructor->publishedCourses as $course) {
            $isEnrolled = false;
            $enrollmentStatus = null;
            $hasAccess = false;
            $isInWishlist = false;

            if ($currentUser) {
                $enrollment = \App\Models\Enrollment::where('user_id', $currentUser->id)
                    ->where('course_id', $course->id)
                    ->first();

                if ($enrollment) {
                    $isEnrolled = true;
                    $enrollmentStatus = $enrollment->status;
                    if (in_array($enrollment->status, ['active', 'completed'])) {
                        $hasAccess = true;
                    }
                }

                $isInWishlist = \App\Models\Wishlist::where('user_id', $currentUser->id)
                    ->where('course_id', $course->id)
                    ->exists();

                if ((int) $course->instructor_id === (int) $currentUser->id) {
                    $hasAccess = true;
                }
            }

            $course->is_enrolled = $isEnrolled;
            $course->enrollment_status = $enrollmentStatus;
            $course->is_in_wishlist = $isInWishlist;
            $course->has_access = $hasAccess;
        }

        return [
            'instructor' => $instructor,
        ];
    }

    public function faqs(int $id, array $params): array
    {
        // 1. Fetch course by ID
        $course = Course::find($id);

        if (!$course) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        // 2. Check if course is published
        if ($course->status !== 'published') {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
        }

        // 3. Query faqs (only active)
        $query = $course->faqs()->where('faqs.status', 'active');

        // Paginate faqs
        $perPage = (int) ($params['per_page'] ?? 10);
        $paginator = $query->paginate($perPage);

        return [
            'paginator' => $paginator,
        ];
    }

    private function resolveOptionalUser()
    {
        $plainAccessToken = request()->bearerToken();

        if (!$plainAccessToken) {
            if (request()->header('Authorization')) {
                return request()->user();
            }
            return null;
        }

        try {
            $tokenPayload = $this->accessTokenService->parseAccessToken($plainAccessToken);
            $session = $this->sessionRepository->findActiveById($tokenPayload['session_id']);

            if (!$session) {
                return null;
            }

            $user = $this->userRepository->findById($tokenPayload['user_id']);

            if (!$user || !$user->isActive()) {
                return null;
            }

            if ((int) $session->user_id !== (int) $user->id) {
                return null;
            }

            return $user;
        } catch (\Exception $exception) {
            // Silence any exceptions during optional auth parsing
            return null;
        }
    }
}
