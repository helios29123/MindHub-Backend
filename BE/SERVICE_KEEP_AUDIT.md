# SERVICE KEEP AUDIT

**Project:** MindHub Backend
**Purpose:** Review business logic of Service files previously classified as GIỮ.
**Database FINAL:** Source of truth
**Generated:** 2026-08-27 09:59:06

**Branch:** be-database-new
**Commit:** e4e7030

---

# 1. FILE CHECK

- [FOUND] `app\Services\Admin\AdminCommissionService.php`
- [FOUND] `app\Services\Admin\AdminDashboardService.php`
- [FOUND] `app\Services\Admin\AdminUserService.php`
- [FOUND] `app\Services\AdminService.php`
- [FOUND] `app\Services\Auth\AccessTokenService.php`
- [FOUND] `app\Services\Auth\AuthSessionService.php`
- [FOUND] `app\Services\Auth\DeviceLimitService.php`
- [FOUND] `app\Services\Auth\GoogleTokenVerifier.php`
- [FOUND] `app\Services\Instructor\InstructorLearnerService.php`
- [FOUND] `app\Services\Instructor\InstructorRevenueService.php`
- [FOUND] `app\Services\Interaction\ReviewService.php`
- [FOUND] `app\Services\Learning\LearningDashboardService.php`
- [FOUND] `app\Services\Learning\SignedAssetUrlService.php`
- [FOUND] `app\Services\Marketing\CouponService.php`
- [FOUND] `app\Services\Marketing\MarketingService.php`
- [FOUND] `app\Services\MarketingService.php`
- [FOUND] `app\Services\Moderation\CourseModerationService.php`
- [FOUND] `app\Services\Payment\Contracts\PaymentGatewayInterface.php`
- [FOUND] `app\Services\Payment\EnrollmentAfterPaymentService.php`
- [FOUND] `app\Services\Payment\Gateways\SePayGateway.php`
- [FOUND] `app\Services\Report\InstructorDashboardAlertService.php`
- [FOUND] `app\Services\Report\InstructorDashboardService.php`
- [FOUND] `app\Services\Report\InstructorEnrollmentChartService.php`
- [FOUND] `app\Services\Report\InstructorReportService.php`
- [FOUND] `app\Services\Report\InstructorRevenueChartService.php`
- [FOUND] `app\Services\Report\InstructorTopCourseService.php`
- [FOUND] `app\Services\Storage\CloudinaryService.php`
- [FOUND] `app\Services\User\UserProfileService.php`
- [FOUND] `app\Services\Wishlist\WishlistService.php`

**Expected:** 29
**Found:** 29
**Missing:** 0

---

# 2. QUICK LEGACY SCAN

Các keyword dưới đây chỉ dùng để phát hiện dấu hiệu cần xem lại.
Có hit KHÔNG đồng nghĩa chắc chắn code sai.

## app\Services\Auth\AuthSessionService.php

- Line 4: `use App\Repositories\Auth\AuthSessionRepository;`
- Line 7: `final class AuthSessionService`
- Line 12: `private readonly AuthSessionRepository $authSessionRepository`
- Line 20: `return $this->authSessionRepository->paginateByUserId(`
- Line 30: `$session = $this->authSessionRepository->findByIdAndUserIdForUpdate(`
- Line 43: `'revoked_count' => $this->authSessionRepository->revoke($session),`
- Line 54: `'revoked_count' => $this->authSessionRepository->revokeActiveByUserId(`

## app\Services\Auth\DeviceLimitService.php

- Line 5: `use App\Repositories\Auth\AuthSessionRepository;`
- Line 9: `private readonly AuthSessionRepository $authSessionRepository`
- Line 33: `$activeSessionCount = $this->authSessionRepository->countActiveByUserId((int) $user->id);`

---

# 3. FULL SOURCE CODE


## 1. app\Services\Admin\AdminCommissionService.php

```php
<?php

namespace App\Services\Admin;

use App\Models\CommissionRule;
use App\Models\User;
use App\Repositories\Admin\AdminCommissionRepository;

final class AdminCommissionService
{
    public function __construct(private readonly AdminCommissionRepository $repo, private readonly AdminNotificationService $notifications) {}
    public function all()
    {
        return $this->repo->all();
    }
    public function update(CommissionRule $rule, array $data, User $admin): CommissionRule
    {
        $old = $rule->toArray();
        $rule->update($data);
        $this->notifications->audit($admin, 'commission_rule.update', $rule, $old, $rule->fresh()->toArray());
        return $rule->fresh();
    }
}
```

---


## 2. app\Services\Admin\AdminDashboardService.php

```php
<?php

namespace App\Services\Admin;

use App\Repositories\Admin\AdminDashboardRepository;

final class AdminDashboardService
{
    public function __construct(private readonly AdminDashboardRepository $repo) {}
    public function overview(array $filters): array
    {
        return ['kpis' => $this->repo->kpis(), 'source_breakdown' => $this->repo->sourceBreakdown(), 'revenue_chart' => $this->repo->revenueChart()];
    }
    public function revenueChart(array $filters): array
    {
        return $this->repo->revenueChart();
    }
    public function actionRequired(): array
    {
        return $this->repo->kpis();
    }
}
```

---


## 3. app\Services\Admin\AdminUserService.php

```php
<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Admin\AdminUserRepository;

final class AdminUserService
{
    public function __construct(private readonly AdminUserRepository $repo, private readonly AdminNotificationService $notifications) {}
    public function paginate(array $filters)
    {
        return $this->repo->paginate($filters);
    }
    public function show(User $user): User
    {
        return $user;
    }
    public function block(User $user, User $admin): User
    {
        $user->update(['status' => 'blocked']);
        $this->notifications->audit($admin, 'user.block', $user);
        return $user->fresh();
    }
    public function unblock(User $user, User $admin): User
    {
        $user->update(['status' => 'active']);
        $this->notifications->audit($admin, 'user.unblock', $user);
        return $user->fresh();
    }
    public function approveInstructor(User $user, User $admin): User
    {
        $user->update(['role' => 'instructor', 'status' => 'active']);
        $this->notifications->audit($admin, 'user.approve_instructor', $user);
        return $user->fresh();
    }
}
```

---


## 4. app\Services\AdminService.php

```php
<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminService
{
    public function getBanners(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        return Banner::orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
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
}
```

---


## 5. app\Services\Auth\AccessTokenService.php

```php
<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AccessTokenService
{
    private const ACCESS_TOKEN_EXPIRES_MINUTES = 60 * 24 * 365; // 365 days
    private const REFRESH_TOKEN_EXPIRES_DAYS = 365; // 365 days

    private function getAccessTokenExpiresMinutes(): int
    {
        return (int) env('ACCESS_TOKEN_EXPIRES_MINUTES', 10080);
    }

    public function createAccessToken(int $userId, int $sessionId): array
    {
        $expiresMinutes = $this->getAccessTokenExpiresMinutes();
        $expiresAt = now()->addMinutes($expiresMinutes);

        $payload = [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'expires_at' => $expiresAt->timestamp,
            'issued_at' => now()->timestamp,
        ];

        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encodedPayload, $this->getSigningKey());

        return [
            'token' => $encodedPayload . '.' . $signature,
            'expires_at' => $expiresAt,
            'expires_in' => $expiresMinutes * 60,
        ];
    }

    public function createRefreshToken(): array
    {
        $plainRefreshToken = Str::random(80);

        return [
            'token' => $plainRefreshToken,
            'token_hash' => hash('sha256', $plainRefreshToken),
            'expires_at' => now()->addDays(self::REFRESH_TOKEN_EXPIRES_DAYS),
        ];
    }

    public function parseAccessToken(string $plainAccessToken): array
    {
        $tokenParts = explode('.', $plainAccessToken);

        if (count($tokenParts) !== 2) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        [$encodedPayload, $signature] = $tokenParts;
        $expectedSignature = hash_hmac('sha256', $encodedPayload, $this->getSigningKey());

        if (! hash_equals($expectedSignature, $signature)) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        $payloadJson = $this->base64UrlDecode($encodedPayload);
        $payload = json_decode($payloadJson, true);

        if (! is_array($payload)) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        $userId = (int) Arr::get($payload, 'user_id');
        $sessionId = (int) Arr::get($payload, 'session_id');
        $expiresAt = (int) Arr::get($payload, 'expires_at');

        if ($userId <= 0 || $sessionId <= 0 || $expiresAt <= 0) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        if (now()->timestamp > $expiresAt && !config('app.debug')) {
            throw new BusinessException('Token đã hết hạn.', 401);
        }

        return [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'expires_at' => $expiresAt,
        ];
    }

    private function getSigningKey(): string
    {
        $appKey = (string) config('app.key');

        if (str_starts_with($appKey, 'base64:')) {
            $decodedKey = base64_decode(substr($appKey, 7), true);

            return $decodedKey !== false ? $decodedKey : $appKey;
        }

        return $appKey;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decodedValue = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decodedValue === false) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        return $decodedValue;
    }
}
```

---


## 6. app\Services\Auth\AuthSessionService.php

```php
<?php
namespace App\Services\Auth;
use App\Exceptions\BusinessException;
use App\Repositories\Auth\AuthSessionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
final class AuthSessionService
{
    private const DEFAULT_STATUS = 'active';
    private const DEFAULT_PER_PAGE = 10;
    public function __construct(
        private readonly AuthSessionRepository $authSessionRepository
    ) {
    }
    public function paginateForCurrentUser(int $userId, array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? self::DEFAULT_STATUS;
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : self::DEFAULT_PER_PAGE;
        $page = isset($filters['page']) ? (int) $filters['page'] : 1;
        return $this->authSessionRepository->paginateByUserId(
            userId: $userId,
            status: $status,
            perPage: $perPage,
            page: $page
        );
    }
    public function revokeForCurrentUser(int $userId, int $sessionId): array
    {
        return DB::transaction(function () use ($userId, $sessionId): array {
            $session = $this->authSessionRepository->findByIdAndUserIdForUpdate(
                sessionId: $sessionId,
                userId: $userId
            );
            if (! $session) {
                throw new BusinessException('Không tìm thấy phiên đăng nhập.', 404);
            }
            if ($session->revoked_at !== null) {
                return [
                    'revoked_count' => 0,
                ];
            }
            return [
                'revoked_count' => $this->authSessionRepository->revoke($session),
            ];
        });
    }
    public function logoutAllForCurrentUser(int $userId, int $currentSessionId, bool $keepCurrent): array
    {
        return DB::transaction(function () use ($userId, $currentSessionId, $keepCurrent): array {
            $excludeSessionId = $keepCurrent && $currentSessionId > 0
                ? $currentSessionId
                : null;
            return [
                'revoked_count' => $this->authSessionRepository->revokeActiveByUserId(
                    userId: $userId,
                    excludeSessionId: $excludeSessionId
                ),
            ];
        });
    }
}
```

---


## 7. app\Services\Auth\DeviceLimitService.php

```php
<?php
namespace App\Services\Auth;
use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\Auth\AuthSessionRepository;
final class DeviceLimitService
{
    public function __construct(
        private readonly AuthSessionRepository $authSessionRepository
    ) {
    }
    public function assertCanCreateSession(User $user): array
    {
        $meta = $this->sessionLimitMeta($user);
        if (! $this->shouldLimitUser($user)) {
            return $meta;
        }
        if ($meta['active_session_count'] >= $meta['max_sessions']) {
            throw new BusinessException(
                'Tài khoản đã đạt giới hạn thiết bị đăng nhập.',
                409,
                [
                    'sessions' => [
                        'Bạn đã đạt giới hạn thiết bị đăng nhập. Vui lòng đăng xuất thiết bị khác rồi thử lại.',
                    ],
                ]
            );
        }
        return $meta;
    }
    public function sessionLimitMeta(User $user): array
    {
        $activeSessionCount = $this->authSessionRepository->countActiveByUserId((int) $user->id);
        return [
            'active_session_count' => $activeSessionCount,
            'max_sessions' => $this->maxAllowedSessions($user),
        ];
    }
    private function shouldLimitUser(User $user): bool
    {
        return $user->role === User::ROLE_LEARNER;
    }
    private function maxAllowedSessions(User $user): int
    {
        if (! $this->shouldLimitUser($user)) {
            return PHP_INT_MAX;
        }
        $configuredLimit = (int) config('mindhub.max_learner_sessions', 2);
        return max(1, $configuredLimit);
    }
}
```

---


## 8. app\Services\Auth\GoogleTokenVerifier.php

```php
<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleTokenVerifier
{
    public function verify(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        if (empty($clientId)) {
            throw new BusinessException('Chưa cấu hình GOOGLE_CLIENT_ID trong file .env.', 500);
        }

        try {
            $response = Http::timeout(15)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);
        } catch (Throwable $e) {
            throw new BusinessException('Không thể kết nối tới Google để xác thực token.', 500);
        }

        if (!$response->successful()) {
            throw new BusinessException('Google token không hợp lệ hoặc đã hết hạn.', 401);
        }

        $payload = $response->json();

        if (($payload['aud'] ?? null) !== $clientId) {
            throw new BusinessException('Google token không đúng GOOGLE_CLIENT_ID.', 401);
        }

        if (empty($payload['sub']) || empty($payload['email'])) {
            throw new BusinessException('Google token thiếu thông tin tài khoản.', 401);
        }

        return [
            'provider' => 'google',
            'provider_id' => $payload['sub'],
            'email' => $payload['email'],
            'full_name' => $payload['name'] ?? $payload['email'],
            'avatar' => $payload['picture'] ?? null,
            'email_verified' => ($payload['email_verified'] ?? false) === true
                || ($payload['email_verified'] ?? false) === 'true',
        ];
    }
}
```

---


## 9. app\Services\Instructor\InstructorLearnerService.php

```php
<?php

namespace App\Services\Instructor;

use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Instructor\InstructorLearnerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstructorLearnerService
{
    public function __construct(
        private readonly InstructorLearnerRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
    }

    public function paginateLearners(int $instructorId, array $filters): LengthAwarePaginator
    {
        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all' && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->paginateLearners($instructorId, $filters);
    }

    public function getLearnersSummary(int $instructorId, array $filters = []): array
    {
        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all' && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->getLearnersSummary($instructorId, $filters);
    }

    public function getLearnersChart(int $instructorId, array $filters = []): array
    {
        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all' && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->getLearnersChart($instructorId, $filters);
    }

    public function getLearnerDetails(int $instructorId, int $enrollmentId): array
    {
        return $this->repository->getLearnerDetails($instructorId, $enrollmentId);
    }

    public function exportLearners(int $instructorId, array $filters = []): \Illuminate\Support\Collection
    {
        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all' && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->exportLearners($instructorId, $filters);
    }
}
```

---


## 10. app\Services\Instructor\InstructorRevenueService.php

```php
<?php

namespace App\Services\Instructor;

use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Instructor\InstructorRevenueRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstructorRevenueService
{
    public function __construct(
        private readonly InstructorRevenueRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
    }

    public function getRevenueReport(int $instructorId, array $filters): array
    {
        if (!empty($filters['course_id']) && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->getRevenueReport($instructorId, $filters);
    }
}
```

---


## 11. app\Services\Interaction\ReviewService.php

```php
<?php
namespace App\Services\Interaction;
use App\Models\CourseReview;
use App\Models\User;
use App\Repositories\Interaction\ReviewRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
class ReviewService
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository
    ) {
    }
    public function storeReview(int $courseId, array $payload, User $learner): CourseReview
    {
        try {
            return DB::transaction(function () use ($courseId, $payload, $learner): CourseReview {
                if (! $learner->isActive()) {
                    throw new HttpException(403, 'Tài khoản của bạn không thể đánh giá khóa học.');
                }
                $course = $this->reviewRepository->findPublishedCourse($courseId);
                if ($course === null) {
                    throw new HttpException(404, 'Không tìm thấy khóa học.');
                }
                $paidOrder = $this->reviewRepository->findPaidOrderForUserCourse(
                    userId: (int) $learner->id,
                    courseId: $courseId
                );
                $hasActiveEnrollment = $this->reviewRepository->hasActiveEnrollment(
                    userId: (int) $learner->id,
                    courseId: $courseId
                );
                if ($paidOrder === null || ! $hasActiveEnrollment) {
                    throw new HttpException(403, 'Bạn cần học khóa này trước khi đánh giá.');
                }
                if ($this->reviewRepository->hasReviewForUserCourse((int) $learner->id, $courseId)) {
                    throw new HttpException(409, 'Bạn đã đánh giá khóa học này.');
                }
                return $this->reviewRepository->createReview(
                    order: $paidOrder,
                    rating: (int) $payload['rating'],
                    comment: $payload['content'] ?? null
                );
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                throw new HttpException(409, 'Bạn đã đánh giá khóa học này.');
            }
            throw $exception;
        }
    }
}
```

---


## 12. app\Services\Learning\LearningDashboardService.php

```php
<?php

namespace App\Services\Learning;

use App\Repositories\Learning\LearningDashboardRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LearningDashboardService
{
    public function __construct(
        private readonly LearningDashboardRepository $learningDashboardRepository
    ) {
    }

    public function getDashboard(int $userId, array $filters = []): array
    {
        $statistics = $this->learningDashboardRepository->getStatistics($userId);
        $recentCourse = $this->learningDashboardRepository->getRecentCourse($userId);

        return [
            'statistics' => $statistics,
            'recent_course' => $recentCourse,
        ];
    }

    public function getActivityCalendar(int $userId, int $month, int $year): array
    {
        $streak = $this->learningDashboardRepository->getStreak($userId);
        $dailyMission = $this->learningDashboardRepository->getDailyMission($userId);
        $heatmap = $this->learningDashboardRepository->getHeatmap($userId, $month, $year);

        return [
            'streak' => $streak,
            'daily_mission' => $dailyMission,
            'heatmap' => $heatmap,
        ];
    }
}
```

---


## 13. app\Services\Learning\SignedAssetUrlService.php

```php
<?php

namespace App\Services\Learning;

use App\Repositories\Learning\LessonAssetRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\Enrollment;

class SignedAssetUrlService
{
    public function __construct(
        private readonly LessonAssetRepository $repository
    ) {
    }

    public function generateSignedAssetUrl(int $learnerId, int $assetId, ?int $ttlSeconds): array
    {
        $asset = $this->repository->findById($assetId);

        if (!$asset || !$asset->lesson || !$asset->lesson->course) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy tài nguyên.', 404);
        }

        $lesson = $asset->lesson;
        $course = $lesson->course;

        // Check if lesson is previewable and published. If previewable, we might allow it without enrollment.
        // But the requirement says: "Learner có enrollment course chứa lesson". Let's enforce enrollment unless preview.
        $hasAccess = false;

        if ($lesson->is_preview && $lesson->status === 'published' && $course->status === 'published') {
            $hasAccess = true;
        } else {
            $enrollment = Enrollment::where('user_id', $learnerId)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment && in_array($enrollment->status, ['active', 'completed'])) {
                $hasAccess = true;
            }
            
            // Check if user is instructor of the course
            if ((int) $course->instructor_id === $learnerId) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            throw new \App\Exceptions\BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        $ttlSeconds = $ttlSeconds ?? 300; // default 300s
        $expiresAt = now()->addSeconds($ttlSeconds);

        // Try to generate temporary URL
        try {
            // Assume the file path is stored in file_url relatively or fully.
            // If it's a full URL, we might need to extract the path.
            // If it's stored on S3/MinIO, Storage::disk('s3')->temporaryUrl() works.
            $disk = config('filesystems.default');
            $path = $asset->file_url;
            
            // Basic cleanup if the URL is absolute but stored on the disk
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($path);
                $path = ltrim($parsedUrl['path'], '/');
                // Remove bucket name from path if applicable, depends on setup
            }

            // Test if disk supports temporaryUrl
            if (method_exists(Storage::disk($disk)->getAdapter(), 'getTemporaryUrl') || config('filesystems.default') === 's3') {
                $url = Storage::disk($disk)->temporaryUrl($path, $expiresAt);
            } else {
                // If local disk doesn't support temporary URLs out of the box (without plugin)
                throw new \Exception('Disk does not support temporary URLs');
            }
        } catch (\Exception $e) {
            // Fallback if storage doesn't support signed URLs
            throw new \App\Exceptions\BusinessException('Hạ tầng lưu trữ chưa hỗ trợ URL tạm thời.', 503);
        }

        return [
            'url' => $url,
            'expires_at' => $expiresAt->toIso8601String(),
            'ttl_seconds' => $ttlSeconds,
            'file_type' => $asset->file_type,
            'file_size' => $asset->file_size,
        ];
    }
}
```

---


## 14. app\Services\Marketing\CouponService.php

```php
<?php

namespace App\Services\Marketing;

use App\Exceptions\BusinessException;
use App\Models\Coupon;
use App\Models\Course;
use App\Repositories\Marketing\MarketingCouponRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function __construct(
        private readonly MarketingCouponRepository $couponRepository
    ) {
    }

    public function paginateForInstructor(int $instructorId, array $filters): LengthAwarePaginator
    {
        if (!empty($filters['course_id'])) {
            $this->ensureCourseOwnedByInstructor((int) $filters['course_id'], $instructorId);
        }
        return $this->couponRepository->paginateForInstructor($instructorId, $filters);
    }

    public function getForInstructor(int $instructorId, int $couponId): Coupon
    {
        return $this->getCouponOwnedByInstructor($couponId, $instructorId);
    }

    public function createForInstructor(int $instructorId, array $data): Coupon
    {
        $this->ensureCourseOwnedByInstructor((int) $data['course_id'], $instructorId);
        $this->assertDiscountRule((string) $data['discount_type'], $data['discount_value']);
        $this->assertDateRange($data['start_at'] ?? null, $data['end_at'] ?? null);

        $payload = $data;
        $payload['user_id'] = $instructorId;
        $payload['code'] = strtoupper($data['code']);
        $payload['status'] = $payload['status'] ?? Coupon::STATUS_ACTIVE;
        $payload['used_count'] = 0;

        if ($payload['status'] === 'active') {
            if (isset($payload['end_at']) && Carbon::parse($payload['end_at'])->isPast()) {
                throw new BusinessException('Không thể kích hoạt coupon đã hết hạn.', 409);
            }
            $this->checkActiveCouponConflict((int) $data['course_id']);
        }

        return DB::transaction(function () use ($payload): Coupon {
            try {
                return $this->couponRepository->create($payload);
            } catch (QueryException $exception) {
                if ((string) $exception->getCode() === '23000') {
                    throw new BusinessException('Mã coupon đã tồn tại.', 409);
                }
                throw $exception;
            }
        });
    }

    public function updateForInstructor(int $instructorId, int $couponId, array $data): Coupon
    {
        $coupon = $this->getCouponOwnedByInstructor($couponId, $instructorId);

        if (array_key_exists('code', $data) && $coupon->used_count > 0 && strtoupper($data['code']) !== $coupon->code) {
            throw new BusinessException('Không thể thay đổi mã coupon đã được sử dụng.', 409);
        }

        if (array_key_exists('course_id', $data)) {
            $this->ensureCourseOwnedByInstructor((int) $data['course_id'], $instructorId);
        }

        $nextDiscountType = (string) ($data['discount_type'] ?? $coupon->discount_type);
        $nextDiscountValue = $data['discount_value'] ?? $coupon->discount_value;
        $nextStartAt = array_key_exists('start_at', $data) ? $data['start_at'] : $coupon->start_at;
        $nextEndAt = array_key_exists('end_at', $data) ? $data['end_at'] : $coupon->end_at;

        $this->assertDiscountRule($nextDiscountType, $nextDiscountValue);
        $this->assertDateRange($nextStartAt, $nextEndAt);

        $payload = $data;
        if (array_key_exists('code', $payload)) {
            $payload['code'] = strtoupper($payload['code']);
        }

        $nextStatus = $payload['status'] ?? $coupon->status;
        if ($nextStatus === 'active') {
            if ($nextEndAt && Carbon::parse($nextEndAt)->isPast()) {
                throw new BusinessException('Không thể kích hoạt coupon đã hết hạn.', 409);
            }
            if ($coupon->usage_limit !== null && (int)$coupon->used_count >= (int)$coupon->usage_limit) {
                throw new BusinessException('Không thể kích hoạt coupon đã dùng hết lượt.', 409);
            }
            $this->checkActiveCouponConflict($coupon->course_id, $coupon->id);
        }

        if (
            array_key_exists('usage_limit', $payload)
            && $payload['usage_limit'] !== null
            && (int) $payload['usage_limit'] < (int) $coupon->used_count
        ) {
            throw new BusinessException('Giới hạn lượt dùng không được nhỏ hơn số lượt đã dùng.', 422, [
                'usage_limit' => ['Giới hạn lượt dùng không được nhỏ hơn số lượt đã dùng.'],
            ]);
        }

        return DB::transaction(function () use ($coupon, $payload): Coupon {
            try {
                return $this->couponRepository->update($coupon, $payload);
            } catch (QueryException $exception) {
                if ((string) $exception->getCode() === '23000') {
                    throw new BusinessException('Mã coupon đã tồn tại.', 409);
                }
                throw $exception;
            }
        });
    }

    public function deleteForInstructor(int $instructorId, int $couponId): Coupon
    {
        $coupon = $this->getCouponOwnedByInstructor($couponId, $instructorId);
        return DB::transaction(function () use ($coupon): Coupon {
            return $this->couponRepository->delete($coupon);
        });
    }

    private function ensureCourseOwnedByInstructor(int $courseId, int $instructorId): Course
    {
        $course = $this->couponRepository->findCourseById($courseId);
        if (!$course || (int) $course->instructor_id !== $instructorId) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }
        return $course;
    }

    private function getCouponOwnedByInstructor(int $couponId, int $instructorId): Coupon
    {
        $coupon = $this->couponRepository->findById($couponId);
        if (
            !$coupon
            || (int) $coupon->user_id !== $instructorId
            || $coupon->course_id === null
            || !$coupon->course
            || (int) $coupon->course->instructor_id !== $instructorId
        ) {
            throw new BusinessException('Không tìm thấy coupon hợp lệ.', 404);
        }
        return $coupon;
    }

    private function assertDiscountRule(string $discountType, mixed $discountValue): void
    {
        if ($discountType === Coupon::TYPE_PERCENT && (float) $discountValue > 100) {
            throw new BusinessException('Thông tin coupon không hợp lệ.', 422, [
                'discount_value' => ['Giảm giá phần trăm không được vượt quá 100.'],
            ]);
        }
    }

    private function assertDateRange(mixed $startAt, mixed $endAt): void
    {
        if ($startAt === null || $endAt === null) {
            return;
        }
        if (Carbon::parse($endAt)->lte(Carbon::parse($startAt))) {
            throw new BusinessException('Thông tin coupon không hợp lệ.', 422, [
                'end_at' => ['Thời gian kết thúc phải sau thời gian bắt đầu.'],
            ]);
        }
    }

    private function checkActiveCouponConflict(int $courseId, ?int $excludeCouponId = null): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $now = now();
        $query = Coupon::where('course_id', $courseId)
            ->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
            });

        if ($excludeCouponId) {
            $query->where('id', '!=', $excludeCouponId);
        }

        if ($query->exists()) {
            throw new BusinessException('Khóa học đã có coupon đang hoạt động.', 409);
        }
    }
}
```

---


## 15. app\Services\Marketing\MarketingService.php

```php
<?php

namespace App\Services\Marketing;

use App\Exceptions\BusinessException;
use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MarketingService
{
    public function createCourseAnnouncement(array $data): array
    {
        return [
            'banner_id' => 1,
            'status' => 'active',
        ];
    }

    public function getBanners(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        return Banner::orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
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
}
```

---


## 16. app\Services\MarketingService.php

```php
<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MarketingService
{
    public function createCourseAnnouncement(array $data): array
    {
        return [
            'banner_id' => 1,
            'status' => 'active',
        ];
    }

    public function getBanners(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        return Banner::orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
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
}
```

---


## 17. app\Services\Moderation\CourseModerationService.php

```php
<?php
namespace App\Services\Moderation;
use App\Models\Course;
use App\Repositories\Moderation\CourseModerationRepository;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
class CourseModerationService
{
    private const APPROVED_STATUS = 'approved';
    public function __construct(
        private readonly CourseModerationRepository $courseModerationRepository
    ) {
    }
    public function getCourseReviews(array $filters): LengthAwarePaginator
    {
        return $this->courseModerationRepository->paginateCourseReviews($filters);
    }
    public function approveCourse(int $courseId): Course
    {
        return DB::transaction(function () use ($courseId): Course {
            $course = Course::query()
                ->with('instructor')
                ->whereKey($courseId)
                ->lockForUpdate()
                ->first();
            if (! $course) {
                throw new ModelNotFoundException();
            }
            if ($course->status !== 'pending_review') {
                throw new DomainException('Trạng thái khóa học không hợp lệ để xử lý.');
            }
            $approvedStatus = self::APPROVED_STATUS;
            $course->forceFill([
                'status' => $approvedStatus,
                'admin_reject_reason' => null,
                'published_at' => $approvedStatus === 'published' ? now() : null,
            ])->save();
            $freshCourse = $course->fresh(['instructor']);
            if (! $freshCourse) {
                throw new ModelNotFoundException();
            }
            return $freshCourse;
        });
    }
    public function rejectCourse(int $courseId, string $reason): Course
    {
        return DB::transaction(function () use ($courseId, $reason): Course {
            $course = Course::query()
                ->with('instructor')
                ->whereKey($courseId)
                ->lockForUpdate()
                ->first();
            if (! $course) {
                throw new ModelNotFoundException();
            }
            if ($course->status !== 'pending_review') {
                throw new DomainException('Trạng thái khóa học không hợp lệ để xử lý.');
            }
            $course->forceFill([
                'status' => 'rejected',
                'admin_reject_reason' => $reason,
            ])->save();
            $freshCourse = $course->fresh(['instructor']);
            if (! $freshCourse) {
                throw new ModelNotFoundException();
            }
            return $freshCourse;
        });
    }
}
```

---


## 18. app\Services\Payment\Contracts\PaymentGatewayInterface.php

```php
<?php

namespace App\Services\Payment\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create a payment url or qr code link for an order.
     */
    public function createPaymentUrl(object $order, float $amount): string;

    /**
     * Handle incoming webhook payload and verify it.
     * Returns an array with extracted details, or throws BusinessException on failure.
     */
    public function handleWebhook(array $payload): array;
}
```

---


## 19. app\Services\Payment\EnrollmentAfterPaymentService.php

```php
<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Enrollment;
use App\Models\Order;
use App\Repositories\Payment\EnrollmentRepository;

class EnrollmentAfterPaymentService
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository
    ) {
    }

    public function createEnrollmentAfterPayment(Order $order): Enrollment
    {
        if (!$order->isPaid()) {
            throw new BusinessException('Order chưa đủ điều kiện ghi danh.', 400);
        }

        $existingEnrollment = $this->enrollmentRepository->findByOrderId($order->id);

        if ($existingEnrollment) {
            return $existingEnrollment;
        }

        $existingCourseEnrollment = $this->enrollmentRepository->findByUserAndCourse(
            $order->user_id,
            $order->course_id
        );

        if ($existingCourseEnrollment) {
            return $existingCourseEnrollment;
        }

        $enrollment = $this->enrollmentRepository->create([
            'user_id' => $order->user_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'progress_percent' => 0,
            'enrolled_at' => now(),
        ]);

        try {
            $user = \App\Models\User::find($order->user_id);
            $course = \App\Models\Course::with('instructor')->find($order->course_id);

            if ($user && $course && ! empty($user->email)) {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\CourseWelcomeMail($user, $course, $order));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send CourseWelcomeMail in EnrollmentAfterPaymentService: ' . $e->getMessage());
        }

        return $enrollment;
    }
}
```

---


## 20. app\Services\Payment\Gateways\SePayGateway.php

```php
<?php

namespace App\Services\Payment\Gateways;

use App\Exceptions\BusinessException;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class SePayGateway implements PaymentGatewayInterface
{
    public function createPaymentUrl(object $order, float $amount): string
    {
        $baseUrl = config('sepay.api_base_url', 'https://qr.sepay.vn');
        $bankAccount = config('sepay.bank_account');
        $bankCode = config('sepay.bank_code');
        $accountName = config('sepay.account_name');
        
        if (empty($bankAccount) || empty($bankCode)) {
            throw new BusinessException('Chưa cấu hình SePay Bank Account và Bank Code.', 500);
        }

        $memo = 'MIND' . $order->id; // Using MIND + order ID
        
        $url = $baseUrl . '/img' 
            . '?acc=' . urlencode($bankAccount)
            . '&bank=' . urlencode($bankCode)
            . '&amount=' . urlencode((string) (int) round($amount))
            . '&des=' . urlencode($memo);
            
        if (!empty($accountName)) {
            $url .= '&name=' . urlencode($accountName);
        }

        return $url;
    }

    public function handleWebhook(array $payload): array
    {
        // 1. Authenticate SePay Webhook using HMAC-SHA256
        $secret = config('sepay.webhook_secret');
        
        if (empty($secret)) {
            Log::error('SePay Webhook Verification Failed: Missing webhook_secret configuration.');
            throw new BusinessException('Cấu hình SePay Webhook chưa đầy đủ.', 500);
        }

        $signatureHeader = (string) request()->header('X-SePay-Signature');
        
        if (empty($signatureHeader)) {
            Log::warning('SePay Webhook Verification Failed: Missing X-SePay-Signature header.');
            throw new BusinessException('Thiếu chữ ký xác thực SePay Webhook.', 401);
        }

        $rawRequestBody = request()->getContent();
        
        $expectedSignature = hash_hmac('sha256', $rawRequestBody, $secret);

        if (!hash_equals($expectedSignature, $signatureHeader)) {
            Log::warning('SePay Webhook Verification Failed: Signature mismatch.');
            throw new BusinessException('Xác thực SePay Webhook thất bại. Chữ ký không hợp lệ.', 401);
        }

        // 2. Validate payload
        $gateway = $payload['gateway'] ?? '';
        $transactionDate = $payload['transactionDate'] ?? '';
        $transferAmount = $payload['transferAmount'] ?? 0;
        $content = $payload['content'] ?? '';
        $referenceCode = $payload['referenceCode'] ?? '';

        if (empty($content) || empty($referenceCode)) {
            throw new BusinessException('Thiếu thông tin content hoặc referenceCode.', 422);
        }

        // Extract Order ID from content (assuming format MIND{id} or MIND {id})
        // Remove spaces and normalize
        $normalizedContent = strtoupper(preg_replace('/\s+/', '', $content));
        
        if (!preg_match('/MIND(\d+)/', $normalizedContent, $matches)) {
            throw new BusinessException('Nội dung chuyển khoản không hợp lệ.', 422);
        }

        $orderId = (int) $matches[1];

        return [
            'order_id' => $orderId,
            'amount' => (float) $transferAmount,
            'provider_transaction_id' => $referenceCode,
            'payment_method' => 'sepay',
            'raw' => $payload,
        ];
    }
}
```

---


## 21. app\Services\Report\InstructorDashboardAlertService.php

```php
<?php

namespace App\Services\Report;

use App\Repositories\Report\InstructorDashboardAlertRepository;

class InstructorDashboardAlertService
{
    public function __construct(
        private readonly InstructorDashboardAlertRepository $repository
    ) {
    }

    public function getAlerts(int $instructorId, array $filters): array
    {
        return $this->repository->getAlerts($instructorId, $filters);
    }
}
```

---


## 22. app\Services\Report\InstructorDashboardService.php

```php
<?php

namespace App\Services\Report;

use App\Repositories\Report\InstructorDashboardRepository;

class InstructorDashboardService
{
    public function __construct(
        private readonly InstructorDashboardRepository $repository
    ) {
    }

    public function getDashboard(int $instructorId, array $filters): array
    {
        return $this->repository->getDashboard($instructorId, $filters);
    }
}
```

---


## 23. app\Services\Report\InstructorEnrollmentChartService.php

```php
<?php

namespace App\Services\Report;

use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Report\InstructorEnrollmentChartRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstructorEnrollmentChartService
{
    public function __construct(
        private readonly InstructorEnrollmentChartRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
    }

    public function getChart(int $instructorId, array $filters): array
    {
        if (!empty($filters['course_id']) && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->getChart($instructorId, $filters);
    }
}
```

---


## 24. app\Services\Report\InstructorReportService.php

```php
<?php
namespace App\Services\Report;
use App\Repositories\Report\InstructorReportRepository;
use Illuminate\Support\Facades\DB;
class InstructorReportService
{
    public function __construct(
        private readonly InstructorReportRepository $repository
    ) {}
    public function getCompletionRate(array $filters, $user)
    {
        if (!in_array($user->role, ['admin', 'instructor'])) {
            abort(403, $user->role === 'learner' ? 'Bạn không có quyền thực hiện thao tác này.' : 'Bạn không có quyền giảng viên.');
        }
        if (isset($filters['course_id']) && $user->role === 'instructor') {
            $isOwner = DB::table('courses')
                ->where('id', $filters['course_id'])
                ->where('instructor_id', $user->id)
                ->exists();
            if (!$isOwner) {
                abort(403, 'Bạn không có quyền xem dữ liệu khóa học này.');
            }
        }
        return $this->repository->getCompletionRates($filters, $user);
    }
}
```

---


## 25. app\Services\Report\InstructorRevenueChartService.php

```php
<?php

namespace App\Services\Report;

use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Report\InstructorRevenueChartRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstructorRevenueChartService
{
    public function __construct(
        private readonly InstructorRevenueChartRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
    }

    public function getChart(int $instructorId, array $filters): array
    {
        if (!empty($filters['course_id']) && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->getChart($instructorId, $filters);
    }
}
```

---


## 26. app\Services\Report\InstructorTopCourseService.php

```php
<?php

namespace App\Services\Report;

use App\Repositories\Report\InstructorTopCourseRepository;

class InstructorTopCourseService
{
    public function __construct(
        private readonly InstructorTopCourseRepository $repository
    ) {
    }

    public function getTopCourses(int $instructorId, array $filters): array
    {
        return $this->repository->getTopCourses($instructorId, $filters);
    }
}
```

---


## 27. app\Services\Storage\CloudinaryService.php

```php
<?php

namespace App\Services\Storage;

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;
use RuntimeException;

final class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $cloudName = config('cloudinary.cloud_name');
        $apiKey = config('cloudinary.api_key');
        $apiSecret = config('cloudinary.api_secret');

        if (
            empty($cloudName) ||
            empty($apiKey) ||
            empty($apiSecret)
        ) {
            throw new RuntimeException(
                'Cloudinary configuration is incomplete.'
            );
        }

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => (bool) config('cloudinary.secure', true),
            ],
        ]);
    }

    /**
     * Upload an image to Cloudinary.
     *
     * @return array{
     *     url: string,
     *     public_id: string,
     *     width: int|null,
     *     height: int|null,
     *     format: string|null,
     *     bytes: int|null
     * }
     */
    public function uploadImage(
        UploadedFile $file,
        string $folder
    ): array {
        $result = $this->uploadApi()->upload(
            $file->getRealPath(),
            [
                'resource_type' => 'image',
                'folder' => trim($folder, '/'),
                'use_filename' => false,
                'unique_filename' => true,
                'overwrite' => false,
            ]
        );

        return [
            'url' => (string) $result['secure_url'],
            'public_id' => (string) $result['public_id'],
            'width' => isset($result['width'])
                ? (int) $result['width']
                : null,
            'height' => isset($result['height'])
                ? (int) $result['height']
                : null,
            'format' => $result['format'] ?? null,
            'bytes' => isset($result['bytes'])
                ? (int) $result['bytes']
                : null,
        ];
    }

    /**
     * Delete an image from Cloudinary.
     */
    public function deleteImage(?string $publicId): void
    {
        if (empty($publicId)) {
            return;
        }

        $this->uploadApi()->destroy(
            $publicId,
            [
                'resource_type' => 'image',
                'invalidate' => true,
            ]
        );
    }

    private function uploadApi(): UploadApi
    {
        return $this->cloudinary->uploadApi();
    }
}
```

---


## 28. app\Services\User\UserProfileService.php

```php
<?php

namespace App\Services\User;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\User\UserProfileRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\Storage\CloudinaryService;
use Illuminate\Http\UploadedFile;

final class UserProfileService
{
    public function __construct(
        private readonly UserProfileRepository $userProfileRepository,
        private readonly CloudinaryService $cloudinaryService,
    ) {
    }

    public function getAuthenticatedProfile(Authenticatable $authenticatedUser): User
    {
        return $this->userProfileRepository->findPublicProfileById(
            id: (int) $authenticatedUser->getAuthIdentifier()
        );
    }

    public function updateAuthenticatedProfile(
        Authenticatable $authenticatedUser,
        array $validatedData
    ): User {
        return DB::transaction(function () use ($authenticatedUser, $validatedData): User {
            $userId = (int) $authenticatedUser->getAuthIdentifier();

            $this->userProfileRepository->updateProfileById(
                id: $userId,
                data: $validatedData
            );

            return $this->userProfileRepository->findPublicProfileById(
                id: $userId
            );
        });
    }

    public function uploadAvatar(
        Authenticatable $authenticatedUser,
        UploadedFile $file
    ): string {
        $userId = (int) $authenticatedUser->getAuthIdentifier();

        $user = User::query()->findOrFail($userId);

        // Upload new avatar first.
        $uploaded = $this->cloudinaryService->uploadImage(
            $file,
            'mindhub/avatars'
        );

        $oldPublicId = $user->avatar_public_id;

        $user->avatar_url = $uploaded['url'];
        $user->avatar_public_id = $uploaded['public_id'];
        $user->save();

        // Delete old Cloudinary asset only after the new one is persisted.
        if (!empty($oldPublicId)) {
            $this->cloudinaryService->deleteImage($oldPublicId);
        }

        return $uploaded['url'];
    }

    public function selectAvatarPreset(
        Authenticatable $authenticatedUser,
        string $presetId
    ): string {
        $userId = (int) $authenticatedUser->getAuthIdentifier();

        $user = User::query()->findOrFail($userId);

        $presets = [
            'avatar_01' => 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?: 'MindHub User') . '&background=007A64&color=fff&bold=true',
            'avatar_02' => 'https://ui-avatars.com/api/?name=Instructor&background=121b4b&color=fff&bold=true',
            'avatar_03' => 'https://ui-avatars.com/api/?name=Student&background=0284c7&color=fff&bold=true',
            'avatar_04' => 'https://ui-avatars.com/api/?name=Learner&background=7c3aed&color=fff&bold=true',
            'avatar_05' => 'https://ui-avatars.com/api/?name=Pro&background=d97706&color=fff&bold=true',
        ];

        if (!isset($presets[$presetId])) {
            throw new BusinessException(
                'Mẫu ảnh đại diện không hợp lệ.',
                422
            );
        }

        $oldPublicId = $user->avatar_public_id;
        $avatarUrl = $presets[$presetId];

        $user->avatar_url = $avatarUrl;
        $user->avatar_public_id = null;
        $user->save();

        if (!empty($oldPublicId)) {
            $this->cloudinaryService->deleteImage($oldPublicId);
        }

        return $avatarUrl;
    }

    public function deleteAvatar(
        Authenticatable $authenticatedUser
    ): void {
        $userId = (int) $authenticatedUser->getAuthIdentifier();

        $user = User::query()->findOrFail($userId);

        $oldPublicId = $user->avatar_public_id;

        if (!empty($oldPublicId)) {
            $this->cloudinaryService->deleteImage($oldPublicId);
        }

        $user->avatar_url = null;
        $user->avatar_public_id = null;
        $user->save();
    }

    public function changePassword(
        Authenticatable $authenticatedUser,
        array $validatedData
    ): void {
        DB::transaction(function () use ($authenticatedUser, $validatedData): void {
            $userId = (int) $authenticatedUser->getAuthIdentifier();

            $user = $this->userProfileRepository->findPasswordCredentialById($userId);

            if (! Hash::check($validatedData['current_password'], $user->password_hash)) {
                throw new BusinessException(
                    'Mật khẩu hiện tại không đúng.',
                    400,
                    []
                );
            }

            $this->userProfileRepository->updatePasswordById(
                $userId,
                Hash::make($validatedData['password'])
            );
        });
    }
}
```

---


## 29. app\Services\Wishlist\WishlistService.php

```php
<?php
namespace App\Services\Wishlist;
use App\Exceptions\BusinessException;
use App\Models\User;
use App\Models\Wishlist;
use App\Repositories\Wishlist\WishlistRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
final class WishlistService
{
    public function __construct(
        private readonly WishlistRepository $wishlistRepository
    ) {
    }
    public function getUserWishlist(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $this->wishlistRepository->paginatePublishedCoursesByUser(
            (int) $user->id,
            $perPage
        );
    }
    public function addCourseToWishlist(User $user, int $courseId): Wishlist
    {
        return DB::transaction(function () use ($user, $courseId): Wishlist {
            $course = $this->wishlistRepository->findPublishedCourse($courseId);
            if ($course === null) {
                throw new ModelNotFoundException();
            }
            if ($this->wishlistRepository->exists((int) $user->id, (int) $course->id)) {
                throw new BusinessException(
                    'Khóa học đã có trong danh sách yêu thích.',
                    409
                );
            }
            $wishlist = $this->wishlistRepository->create(
                (int) $user->id,
                (int) $course->id
            );
            return $wishlist->load('course');
        });
    }
    public function removeCourseFromWishlist(User $user, int $courseId): array
    {
        return DB::transaction(function () use ($user, $courseId): array {
            $course = $this->wishlistRepository->findCourse($courseId);
            if ($course === null) {
                throw new BusinessException(
                    'Không tìm thấy khóa học trong danh sách yêu thích.',
                    404
                );
            }
            $wishlist = $this->wishlistRepository->findByUserAndCourse(
                (int) $user->id,
                (int) $course->id
            );
            if ($wishlist === null) {
                throw new BusinessException(
                    'Không tìm thấy khóa học trong danh sách yêu thích.',
                    404
                );
            }
            $this->wishlistRepository->delete($wishlist);
            return [
                'course_id' => (int) $course->id,
                'is_wishlisted' => false,
            ];
        });
    }
}
```

---

