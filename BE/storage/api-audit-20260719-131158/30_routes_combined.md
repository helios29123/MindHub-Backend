# ROUTES COMBINED


---

## routes\api.php

```php
<?php
require __DIR__ . '/api/auth.php';

require __DIR__ . '/api/user.php';

// Sau nﾃy lﾃm module nﾃo thﾃｬ m盻・thﾃｪm dﾃｲng tﾆｰﾆ｡ng 盻ｩng
require __DIR__ . '/api/catalog.php';
require __DIR__ . '/api/course.php';
require __DIR__ . '/api/instructor.php';
require __DIR__ . '/api/quiz.php';
require __DIR__ . '/api/interaction.php';
require __DIR__ . '/api/admin.php';
require __DIR__ . '/api/marketing.php';
require __DIR__ . '/api/wishlist.php';
require __DIR__ . '/api/payment.php';
require __DIR__ . '/api/learning.php';

require __DIR__ . '/api/report.php';
```

---

## routes\api\admin.php

```php
<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCourseApprovalController;
use App\Http\Controllers\AdminCreditPackageController;
use App\Http\Controllers\AdminInstructorCreditController;
use App\Http\Controllers\AdminModerationController;
use App\Http\Controllers\InstructorUpgradeController;
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'active.user', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */
        Route::get('/orders', [AdminController::class, 'orders']);

        // Health-check k蘯ｿt n盻訴 admin (frontend: ApiService.verifyAdminAuthConnection)
        Route::get('/test', fn () => response()->json([
            'success' => true,
            'data' => ['authenticated' => true, 'system_healthy' => true],
        ]));

        /*
        |--------------------------------------------------------------------------
        | Course moderation / review
        |--------------------------------------------------------------------------
        */
        Route::get('/course-reviews', [AdminModerationController::class, 'pendingCourses']);

        /*
         * Rule m盻嬖:
         * Admin approve/publish thﾃnh cﾃｴng thﾃｬ m盻嬖 tr盻ｫ 1 lﾆｰ盻｣t t蘯｡o khﾃｳa h盻皇.
         * Vﾃｬ v蘯ｭy route approve/reject chuy盻ハ sang AdminCourseApprovalController.
         */
        Route::patch('/courses/{courseId}/approve', [AdminCourseApprovalController::class, 'approve'])
            ->whereNumber('courseId');

        Route::patch('/courses/{courseId}/reject', [AdminCourseApprovalController::class, 'reject'])
            ->whereNumber('courseId');

        Route::patch('/moderation/items/{id}', [AdminModerationController::class, 'moderateItem'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Credit packages - Admin t蘯｡o/s盻ｭa/xﾃｳa m盻［ gﾃｳi lﾆｰ盻｣t
        |--------------------------------------------------------------------------
        */
        Route::get('/credit-packages', [AdminCreditPackageController::class, 'index']);

        Route::post('/credit-packages', [AdminCreditPackageController::class, 'store']);

        Route::patch('/credit-packages/{packageId}', [AdminCreditPackageController::class, 'update'])
            ->whereNumber('packageId');

        Route::delete('/credit-packages/{packageId}', [AdminCreditPackageController::class, 'destroy'])
            ->whereNumber('packageId');

        /*
        |--------------------------------------------------------------------------
        | Instructor credits - Admin xem/c盻冢g/tr盻ｫ lﾆｰ盻｣t th盻ｧ cﾃｴng
        |--------------------------------------------------------------------------
        */
        Route::get('/instructors/{instructorId}/credits', [AdminInstructorCreditController::class, 'show'])
            ->whereNumber('instructorId');

        Route::get('/instructors/{instructorId}/credit-transactions', [AdminInstructorCreditController::class, 'transactions'])
            ->whereNumber('instructorId');

        Route::post('/instructors/{instructorId}/credits/adjust', [AdminInstructorCreditController::class, 'adjust'])
            ->whereNumber('instructorId');

        /*
        |--------------------------------------------------------------------------
        | Marketing / Campaigns / Banners
        |--------------------------------------------------------------------------
        */
        Route::match(['get', 'post'], '/campaigns', [MarketingController::class, 'banners']);

        Route::match(['get', 'put', 'patch', 'delete'], '/campaigns/{id}', [MarketingController::class, 'banners'])
            ->whereNumber('id');

        Route::match(['get', 'post'], '/banners', [AdminController::class, 'banners']);

        Route::match(['get', 'put', 'patch', 'delete'], '/banners/{id}', [AdminController::class, 'banners'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */
        Route::get('/categories', [AdminController::class, 'categories']);

        Route::post('/categories', [AdminController::class, 'storeCategory']);

        Route::get('/categories/{id}', [AdminController::class, 'showCategory'])
            ->whereNumber('id');

        Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])
            ->whereNumber('id');

        Route::patch('/categories/{id}', [AdminController::class, 'updateCategory'])
            ->whereNumber('id');

        Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Courses
        |--------------------------------------------------------------------------
        */
        Route::get('/courses', [AdminController::class, 'courses']);

        Route::get('/courses/{id}', [AdminController::class, 'showCourse'])
            ->whereNumber('id');

        Route::patch('/courses/{id}', [AdminController::class, 'updateCourse'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        Route::get('/users', [AdminController::class, 'users']);

        Route::post('/users', [AdminController::class, 'storeUser']);

        Route::get('/users/{id}', [AdminController::class, 'showUser'])
            ->whereNumber('id');

        Route::put('/users/{id}', [AdminController::class, 'updateUser'])
            ->whereNumber('id');

        Route::patch('/users/{id}', [AdminController::class, 'updateUser'])
            ->whereNumber('id');

        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        Route::get('/roles', [AdminController::class, 'roles']);

        Route::post('/roles', [AdminController::class, 'roles']);

        Route::get('/roles/{id}', [AdminController::class, 'roles'])
            ->whereNumber('id');

        Route::put('/roles/{id}', [AdminController::class, 'roles'])
            ->whereNumber('id');

        Route::patch('/roles/{id}', [AdminController::class, 'roles'])
            ->whereNumber('id');

        Route::delete('/roles/{id}', [AdminController::class, 'roles'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Instructor upgrade requests
        |--------------------------------------------------------------------------
        */
        Route::get('/instructor-upgrade-requests', [InstructorUpgradeController::class, 'adminIndex']);

        Route::get('/instructor-upgrade-requests/{userId}', [InstructorUpgradeController::class, 'adminShow'])
            ->whereNumber('userId');

        Route::patch('/instructor-upgrade-requests/{userId}/approve', [InstructorUpgradeController::class, 'approve'])
            ->whereNumber('userId');

        Route::patch('/instructor-upgrade-requests/{userId}/reject', [InstructorUpgradeController::class, 'reject'])
            ->whereNumber('userId');
    });
```

---

## routes\api\auth.php

```php
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Form ﾄ惰ハg kﾃｽ h盻皇 viﾃｪn
    Route::post('register/learner', [AuthController::class, 'registerLearner']);

    // Form ﾄ惰ハg kﾃｽ gi蘯｣ng viﾃｪn
    Route::post('register/instructor', [AuthController::class, 'registerInstructor']);

    // Gi盻ｯ route cﾅｩ n蘯ｿu frontend ﾄ疎ng dﾃｹng /register
    Route::post('register', [AuthController::class, 'registerLearner']);

    // Xﾃ｡c th盻ｱc email
    Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->name('auth.verify-email');

    Route::post('verify-email/resend', [AuthController::class, 'resendVerifyEmail']);

    Route::post('login', [AuthController::class, 'login']);
    Route::post('google', [AuthController::class, 'googleLogin']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth.session');
});
```

---

## routes\api\catalog.php

```php
<?php

use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [CatalogController::class, 'home']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/courses', [CatalogController::class, 'searchCourses']);
Route::get('/courses/sort', [CatalogController::class, 'sortCourses']);
Route::get('/courses/featured', [CatalogController::class, 'featuredCourses']);
Route::get('/courses/latest', [CatalogController::class, 'latestCourses']);
Route::get('/instructors/featured', [CatalogController::class, 'featuredInstructors']);
Route::get('/search/suggestions', [CatalogController::class, 'searchSuggestions']);
```

---

## routes\api\course.php

```php
<?php

use App\Http\Controllers\CoursePublicController;
use Illuminate\Support\Facades\Route;

Route::get('/courses/{slug}', [CoursePublicController::class, 'show']);
Route::get('/courses/{id}/outline', [CoursePublicController::class, 'outline'])->where('id', '[0-9]+');
Route::get('/lessons/{id}/preview', [CoursePublicController::class, 'previewLesson'])->where('id', '[0-9]+');
Route::get('/courses/{id}/reviews', [CoursePublicController::class, 'reviews'])->where('id', '[0-9]+');
Route::get('/instructors/{id}', [CoursePublicController::class, 'showInstructor'])->where('id', '[0-9]+');
Route::get('/courses/{id}/faqs', [CoursePublicController::class, 'faqs'])->where('id', '[0-9]+');
Route::get('/courses/{courseId}/related', [CoursePublicController::class, 'relatedCourses'])->where('courseId', '[0-9]+');
```

---

## routes\api\instructor.php

```php
<?php
use App\Http\Controllers\InstructorCreditController;
use App\Http\Controllers\InstructorCourseController;
use App\Http\Controllers\InstructorUpgradeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\InstructorWithdrawalController;
use App\Http\Controllers\InstructorProfileController;
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
Route::middleware(['auth.session', 'active.user', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        /*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
        Route::get('/credit-packages', [InstructorCreditController::class, 'packages']);
        Route::get('/course-credits', [InstructorCreditController::class, 'balance']);
        Route::get('/credit-transactions', [InstructorCreditController::class, 'transactions']);
        Route::post('/credit-orders', [InstructorCreditController::class, 'createOrder']);
        /*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
        Route::post('/courses/draft', [InstructorCourseController::class, 'storeDraft']);
        Route::post('/courses', [InstructorCourseController::class, 'store']);
        Route::get('/courses/{id}', [InstructorCourseController::class, 'show'])
            ->whereNumber('id');

        Route::get('/courses/{id}/content', [InstructorCourseController::class, 'content'])
            ->whereNumber('id');

        Route::patch('/courses/{id}/draft', [InstructorCourseController::class, 'updateDraft'])
            ->whereNumber('id');
        Route::patch('/courses/{id}', [InstructorCourseController::class, 'update'])
            ->whereNumber('id');
        Route::post('/courses/{id}/submit', [InstructorCourseController::class, 'submitForReview'])
            ->whereNumber('id');
        Route::get('/courses/{id}/review-notes', [InstructorCourseController::class, 'reviewNotes'])
            ->whereNumber('id');
        Route::get('/courses/{id}/learners', [InstructorCourseController::class, 'learners'])
            ->whereNumber('id');
        Route::get('/courses/{courseId}/checklist', [InstructorCourseController::class, 'checklist'])
            ->whereNumber('courseId');
        /*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
        Route::get('/lessons', [InstructorCourseController::class, 'indexLessons']);
        Route::post('/lessons', [InstructorCourseController::class, 'storeLesson']);
        Route::get('/lessons/{id}', [InstructorCourseController::class, 'showLesson'])
            ->whereNumber('id');
        Route::patch('/lessons/{id}/preview', [InstructorCourseController::class, 'togglePreview'])
            ->whereNumber('id');
        Route::match(['put', 'patch'], '/lessons/{id}', [InstructorCourseController::class, 'updateLesson'])
            ->whereNumber('id');
        Route::delete('/lessons/{id}', [InstructorCourseController::class, 'destroyLesson'])
            ->whereNumber('id');
        Route::post('/lessons/{id}/video', [InstructorCourseController::class, 'uploadVideo'])
            ->whereNumber('id');
        Route::post('/lessons/{id}/assets', [InstructorCourseController::class, 'uploadAsset'])
            ->whereNumber('id');
        /*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
        Route::get('/sections', [InstructorCourseController::class, 'sections']);
        Route::post('/sections', [InstructorCourseController::class, 'storeSection']);
        Route::get('/sections/{id}', [InstructorCourseController::class, 'showSection'])
            ->whereNumber('id');
        Route::put('/sections/{id}', [InstructorCourseController::class, 'updateSection'])
            ->whereNumber('id');
        Route::patch('/sections/{id}', [InstructorCourseController::class, 'updateSection'])
            ->whereNumber('id');
        Route::delete('/sections/{id}', [InstructorCourseController::class, 'deleteSection'])
            ->whereNumber('id');
        /*
        |--------------------------------------------------------------------------
        | Q&A / comments management
        |--------------------------------------------------------------------------
        */
        Route::get('/questions/summary', [InteractionController::class, 'instructorQuestionSummary']);
        Route::get('/questions/course-options', [InteractionController::class, 'instructorQuestionCourseOptions']);
        Route::get('/questions/lesson-options', [InteractionController::class, 'instructorQuestionLessonOptions']);
        Route::get('/questions', [InteractionController::class, 'instructorQuestions']);
        Route::get('/questions/{id}', [InteractionController::class, 'showInstructorQuestion'])
            ->whereNumber('id');
        /*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
        /*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
        /*
        |--------------------------------------------------------------------------
        | Instructor profile management
        |--------------------------------------------------------------------------
        */
        Route::get('/profile', [InstructorProfileController::class, 'show']);
        Route::patch('/profile/account', [InstructorProfileController::class, 'updateAccount']);
        Route::patch('/profile/introduction', [InstructorProfileController::class, 'updateIntroduction']);
        Route::patch('/profile/expertise', [InstructorProfileController::class, 'updateExpertise']);
        Route::get('/profile/completion', [InstructorProfileController::class, 'completion']);
        Route::get('/revenue', [InstructorCourseController::class, 'revenue']);
        Route::get('/withdrawals/summary', [InstructorWithdrawalController::class, 'summary']);
        Route::get('/withdrawals', [InstructorWithdrawalController::class, 'index']);
        Route::post('/withdrawals', [InstructorWithdrawalController::class, 'store']);
        Route::get('/withdrawals/{id}', [InstructorWithdrawalController::class, 'show'])
            ->whereNumber('id');
        Route::get('/payout-accounts', [InstructorWithdrawalController::class, 'payoutAccounts']);
        /*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
        Route::match(['get', 'post'], '/quizzes', [InstructorCourseController::class, 'manageQuizzes']);
        Route::match(['get', 'put', 'patch', 'delete'], '/quizzes/{id}', [InstructorCourseController::class, 'manageQuizzes'])
            ->whereNumber('id');
    });
/*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner'])
    ->group(function (): void {
        Route::get('/me/instructor-upgrade', [InstructorUpgradeController::class, 'myApplication']);
        Route::post('/me/instructor-upgrade', [InstructorUpgradeController::class, 'store']);
        Route::put('/me/instructor-upgrade', [InstructorUpgradeController::class, 'update']);
    });
/*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
Route::middleware(['auth.session', 'active.user', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        /*
|--------------------------------------------------------------------------
| Ghi chﾃｺ
|--------------------------------------------------------------------------
| N盻冓 dung mﾃｴ t蘯｣ cﾅｩ b盻・l盻擁 mﾃ｣ hﾃｳa, ﾄ妥｣ ﾄ柁ｰ盻｣c chu蘯ｩn hﾃｳa l蘯｡i.
*/
        Route::get('/dashboard', [ReportController::class, 'instructorDashboard'])
            ->name('instructor.dashboard');

        Route::get('/courses', [InstructorCourseController::class, 'index'])
            ->name('instructor.courses.index');

        Route::get('/learners', [InstructorCourseController::class, 'allLearners'])
            ->name('instructor.learners.index');

        Route::get('/reports/revenue-chart', [ReportController::class, 'instructorRevenueChart'])
            ->name('instructor.reports.revenue-chart');

        Route::get('/reports/enrollment-chart', [ReportController::class, 'instructorEnrollmentChart'])
            ->name('instructor.reports.enrollment-chart');

        Route::get('/reports/top-courses', [ReportController::class, 'instructorTopCourses'])
            ->name('instructor.reports.top-courses');

        Route::get('/withdrawals/summary', [InstructorCourseController::class, 'withdrawSummary'])
            ->name('instructor.withdrawals.summary');

        Route::get('/withdrawals', [InstructorCourseController::class, 'withdrawals'])
            ->name('instructor.withdrawals.index');

        Route::get('/questions', [InteractionController::class, 'instructorQuestions'])
            ->name('instructor.questions.index');

        Route::get('/dashboard/alerts', [ReportController::class, 'instructorDashboardAlerts'])
            ->name('instructor.dashboard.alerts');
    });
```

---

## routes\api\interaction.php

```php
<?php
use App\Http\Controllers\InteractionController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'role:learner'])
    ->group(function (): void {
        Route::match(['get', 'post'], '/lessons/{id}/comments', [InteractionController::class, 'lessonComments'])
            ->where('id', '[0-9]+');
        Route::post('/courses/{id}/reviews', [InteractionController::class, 'storeReview'])
            ->where('id', '[0-9]+');
    });
Route::middleware(['auth.session', 'active.user', 'role:instructor'])
    ->group(function (): void {
        Route::post('/comments/{id}/replies', [InteractionController::class, 'replyComment'])
            ->where('id', '[0-9]+');
    });
```

---

## routes\api\learning.php

```php
<?php
use App\Http\Controllers\LearningController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'active.user'])->group(function (): void {
    Route::get('/learn/lessons/{id}/check-access', [LearningController::class, 'canAccessLesson'])->whereNumber('id');
});
Route::middleware(['auth.session', 'active.user', 'role:learner'])->group(function (): void {
    Route::get('/me/courses', [LearningController::class, 'myCourses']);
    Route::get('/learn/lessons/{id}', [LearningController::class, 'showLesson'])->whereNumber('id');
    Route::get('/learn/lessons/{id}/video-url', [LearningController::class, 'signedLessonVideoUrl'])
        ->whereNumber('id');
    Route::get('/learn/courses/{id}/outline', [LearningController::class, 'outline'])->whereNumber('id');
    Route::patch('/learn/lessons/{id}/progress', [LearningController::class, 'saveVideoProgress'])->whereNumber('id');
    Route::get('/learn/resume', [LearningController::class, 'resume']);
    Route::patch('/learn/lessons/{id}/complete', [LearningController::class, 'completeLesson'])->whereNumber('id');
    Route::get('/learn/courses/{id}/progress', [LearningController::class, 'courseProgress'])->whereNumber('id');
    Route::get('/learning-logs/my', [LearningController::class, 'learningLogs']);
    Route::get('/learn/assets/{id}/download', [LearningController::class, 'downloadAsset'])->whereNumber('id');
    Route::get('/learn/lessons/{id}/next', [LearningController::class, 'nextLesson'])->whereNumber('id');
    Route::get('/me/recommendations/rule-based', [LearningController::class, 'ruleBasedRecommendations']);
    Route::get('/me/learning-path/next', [LearningController::class, 'nextLearningPath']);
    Route::get('/me/dynamic-alerts', [LearningController::class, 'dynamicAlerts']);
    Route::post('/learn/assets/{assetId}/signed-url', [LearningController::class, 'signedAssetUrl'])->where('assetId', '[0-9]+');
    Route::get('/learn/lessons/{lessonId}/watermark-info', [LearningController::class, 'watermarkInfo'])->where('lessonId', '[0-9]+');
});
Route::middleware(['auth.session', 'active.user', 'role:learner'])->group(function () {
    Route::get('/me/learning-dashboard', [LearningController::class, 'dashboard']);
});

/*
|--------------------------------------------------------------------------
| Stream video bﾃi h盻皇 (private)
|--------------------------------------------------------------------------
| KHﾃ年G dﾃｹng auth.session vﾃｬ th蘯ｻ <video> c盻ｧa trﾃｬnh duy盻㏄ khﾃｴng g盻ｭi ﾄ柁ｰ盻｣c
| header Authorization: Bearer. B蘯｣o m蘯ｭt d盻ｱa trﾃｪn URL ﾄ妥｣ kﾃｽ (temporarySignedRoute)
| + tham s盻・u (learnerId) n蘯ｱm trong ch盻ｯ kﾃｽ, vﾃ service v蘯ｫn ki盻ノ tra enrollment.
| Middleware 'signed' t盻ｱ ﾄ黛ｻ冢g 403 n蘯ｿu ch盻ｯ kﾃｽ sai/h蘯ｿt h蘯｡n.
*/
Route::get('/learn/lessons/{id}/stream', [LearningController::class, 'streamLessonVideo'])
    ->name('learn.lessons.stream')
    ->whereNumber('id');
```

---

## routes\api\marketing.php

```php
<?php
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'active.user', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        Route::post('/course-announcements', [MarketingController::class, 'courseAnnouncements']);
        /*
        |--------------------------------------------------------------------------
        | Instructor coupon management
        |--------------------------------------------------------------------------
        */
        Route::get('/coupons/summary', [MarketingController::class, 'instructorCouponSummary']);
        Route::get('/coupons/course-options', [MarketingController::class, 'couponCourseOptions']);
        Route::get('/coupons', [MarketingController::class, 'instructorCoupons']);
        Route::post('/coupons', [MarketingController::class, 'storeInstructorCoupon']);
        Route::get('/coupons/{id}', [MarketingController::class, 'showInstructorCoupon'])
            ->whereNumber('id');
        Route::patch('/coupons/{id}', [MarketingController::class, 'updateInstructorCoupon'])
            ->whereNumber('id');
        Route::patch('/coupons/{id}/status', [MarketingController::class, 'updateInstructorCouponStatus'])
            ->whereNumber('id');
        Route::delete('/coupons/{id}', [MarketingController::class, 'destroyInstructorCoupon'])
            ->whereNumber('id');
    });
```

---

## routes\api\payment.php

```php
<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Order APIs
|--------------------------------------------------------------------------
| learner/member/instructor ﾄ黛ｻ「 cﾃｳ th盻・t蘯｡o order mua khﾃｳa h盻皇.
| Instructor ﾄ柁ｰ盻｣c mua khﾃｳa h盻皇 c盻ｧa gi蘯｣ng viﾃｪn khﾃ｡c.
| Instructor khﾃｴng ﾄ柁ｰ盻｣c mua khﾃｳa h盻皇 c盻ｧa chﾃｭnh mﾃｬnh, rule nﾃy x盻ｭ lﾃｽ trong service.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner,member,instructor'])
    ->group(function (): void {
        Route::post('/orders', [PaymentController::class, 'storeOrder']);

        Route::post('/orders/apply-coupon', [PaymentController::class, 'applyCoupon']);

        Route::post('/payments', [PaymentController::class, 'storePayment']);

        Route::get('/orders/my', [PaymentController::class, 'myOrders']);

        Route::get('/orders/{id}', [PaymentController::class, 'showOrder'])
            ->whereNumber('id');

        Route::patch('/orders/{orderId}/cancel', [PaymentController::class, 'cancelOrder'])
            ->whereNumber('orderId');

        Route::post('/orders/{orderId}/retry-payment', [PaymentController::class, 'retryPayment'])
            ->whereNumber('orderId');
    });

/*
|--------------------------------------------------------------------------
| VNPAY create payment URL
|--------------------------------------------------------------------------
| learner/member/instructor ﾄ黛ｻ「 ﾄ柁ｰ盻｣c t蘯｡o URL thanh toﾃ｡n cho order c盻ｧa chﾃｭnh mﾃｬnh.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner,member,instructor'])
    ->group(function (): void {
        Route::post('/payments/vnpay/create', [PaymentController::class, 'createVnpayPayment']);
    });

/*
|--------------------------------------------------------------------------
| Admin payment webhook
|--------------------------------------------------------------------------
*/
Route::middleware(['auth.session', 'active.user', 'role:admin'])
    ->group(function (): void {
        Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
    });

/*
|--------------------------------------------------------------------------
| VNPAY return URL
|--------------------------------------------------------------------------
*/
Route::get('/payments/vnpay-return', [PaymentController::class, 'vnpayReturn']);
```

---

## routes\api\quiz.php

```php
<?php

use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'role:learner'])
    ->group(function (): void {
        Route::post('/quizzes/{id}/attempts', [QuizController::class, 'storeAttempt'])
            ->where('id', '[0-9]+');
        
        Route::get('/quiz-attempts/{id}', [QuizController::class, 'showAttempt'])
            ->where('id', '[0-9]+');
    });
Route::middleware(['auth.session', 'active.user', 'role:learner'])
    ->get('/courses/{id}/completion-status', [QuizController::class, 'completionStatus']);
```

---

## routes\api\report.php

```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
Route::middleware(['auth.session', 'role:instructor'])->prefix('instructor/reports')->group(function () {
    Route::get('/completion-rate', [ReportController::class, 'completionRate']);
    Route::get('/inactive-learners', [ReportController::class, 'inactiveLearners']);
});

Route::middleware(['auth.session', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        Route::get('/courses/{id}/dashboard', [ReportController::class, 'courseDashboard'])
            ->where('id', '[0-9]+');
        Route::get('/courses/{courseId}/learner-risk', [ReportController::class, 'learnerRisk'])
            ->where('courseId', '[0-9]+');
        Route::get('/courses/{courseId}/analytics', [ReportController::class, 'courseAnalytics'])
            ->where('courseId', '[0-9]+');
    });

Route::middleware(['auth.session', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        Route::get('/dashboard', [ReportController::class, 'dashboard']);
    });

Route::middleware(['auth.session', 'role:admin'])
    ->prefix('admin/reports')
    ->group(function (): void {
        Route::get('/top-courses', [ReportController::class, 'topCourses']);
        Route::get('/instructors', [ReportController::class, 'topInstructors']);
        Route::get('/revenue', [ReportController::class, 'revenueReport']);
    });
```

---

## routes\api\user.php

```php
<?php

use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'active.user', 'role:learner,instructor,admin'])
    ->prefix('users')
    ->group(function (): void {
        Route::get('me', [UserProfileController::class, 'me']);
        Route::patch('me', [UserProfileController::class, 'updateMe']);
        Route::patch('me/password', [UserProfileController::class, 'changePassword']);
    });
```

---

## routes\api\wishlist.php

```php
<?php
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'active.user', 'role:learner'])
    ->prefix('wishlists')
    ->group(function (): void {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{courseId}', [WishlistController::class, 'destroy']);
    });
```

---

## routes\console.php

```php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command('orders:expire-pending')
    ->hourly()
    ->withoutOverlapping();
```

---

## routes\web.php

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
```
