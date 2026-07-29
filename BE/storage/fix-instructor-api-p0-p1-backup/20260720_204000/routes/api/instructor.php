<?php
use App\Http\Controllers\InstructorCreditController;
use App\Http\Controllers\InstructorCourseController;
use App\Http\Controllers\InstructorUpgradeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\InstructorWithdrawalController;
use App\Http\Controllers\InstructorProfileController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\InstructorCouponController;
use App\Http\Controllers\InstructorPayoutAccountController;
use App\Http\Controllers\InstructorNotificationController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
*/
Route::middleware(['auth.session', 'active.user', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        /*
|--------------------------------------------------------------------------
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
*/
        Route::get('/credit-packages', [InstructorCreditController::class, 'packages']);
        Route::get('/course-credits', [InstructorCreditController::class, 'balance']);
        Route::get('/credit-transactions', [InstructorCreditController::class, 'transactions']);
        Route::post('/credit-orders', [InstructorCreditController::class, 'createOrder']);
        /*
|--------------------------------------------------------------------------
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
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
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
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
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
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
        Route::get('/questions', [InteractionController::class, 'instructorQuestions'])->name('instructor.questions.index');
        Route::get('/questions/{id}', [InteractionController::class, 'showInstructorQuestion'])
            ->whereNumber('id');
        Route::post('/questions/{id}/reply', [InteractionController::class, 'replyInstructorQuestion'])
            ->whereNumber('id');
        Route::patch('/questions/{id}/hide', [InteractionController::class, 'hideInstructorQuestion'])
            ->whereNumber('id');
        Route::patch('/questions/{id}/show', [InteractionController::class, 'showHiddenInstructorQuestion'])
            ->whereNumber('id');
        Route::delete('/questions/{id}', [InteractionController::class, 'deleteInstructorQuestion'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Coupons management
        |--------------------------------------------------------------------------
        */
        Route::get('/coupons/summary', [InstructorCouponController::class, 'summary']);
        Route::get('/coupons/course-options', [InstructorCouponController::class, 'courseOptions']);
        Route::get('/coupons/check-code', [InstructorCouponController::class, 'checkCode']);
        Route::get('/coupons', [InstructorCouponController::class, 'index']);
        Route::post('/coupons', [InstructorCouponController::class, 'store']);
        Route::get('/coupons/{id}', [InstructorCouponController::class, 'show'])->whereNumber('id');
        Route::patch('/coupons/{id}', [InstructorCouponController::class, 'update'])->whereNumber('id');
        Route::patch('/coupons/{id}/enable', [InstructorCouponController::class, 'enable'])->whereNumber('id');
        Route::patch('/coupons/{id}/disable', [InstructorCouponController::class, 'disable'])->whereNumber('id');
        Route::patch('/coupons/{id}/status', [InstructorCouponController::class, 'updateStatus'])->whereNumber('id');
        Route::delete('/coupons/{id}', [InstructorCouponController::class, 'destroy'])->whereNumber('id');
        /*
|--------------------------------------------------------------------------
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
*/
        /*
|--------------------------------------------------------------------------
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
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
        Route::get('/withdrawals/summary', [InstructorWithdrawalController::class, 'summary'])
            ->name('instructor.withdrawals.summary');
        Route::get('/withdrawals', [InstructorWithdrawalController::class, 'index'])
            ->name('instructor.withdrawals.index');
        Route::post('/withdrawals', [InstructorWithdrawalController::class, 'store']);
        Route::get('/withdrawals/{id}', [InstructorWithdrawalController::class, 'show'])
            ->whereNumber('id');
        Route::patch('/withdrawals/{id}/cancel', [InstructorWithdrawalController::class, 'cancel'])
            ->whereNumber('id');
        /*
        |--------------------------------------------------------------------------
        | Payout accounts management
        |--------------------------------------------------------------------------
        */
        Route::get('/payout-accounts', [InstructorPayoutAccountController::class, 'index']);
        Route::get('/payout-accounts/default', [InstructorPayoutAccountController::class, 'default']);
        Route::get('/payout-accounts/{id}', [InstructorPayoutAccountController::class, 'show'])->whereNumber('id');
        Route::post('/payout-accounts', [InstructorPayoutAccountController::class, 'store']);
        Route::patch('/payout-accounts/{id}', [InstructorPayoutAccountController::class, 'update'])->whereNumber('id');
        Route::patch('/payout-accounts/{id}/set-default', [InstructorPayoutAccountController::class, 'setDefault'])->whereNumber('id');
        Route::patch('/payout-accounts/{id}/disable', [InstructorPayoutAccountController::class, 'disable'])->whereNumber('id');
        Route::delete('/payout-accounts/{id}', [InstructorPayoutAccountController::class, 'destroy'])->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Notifications management
        |--------------------------------------------------------------------------
        */
        Route::get('/notifications/unread-count', [InstructorNotificationController::class, 'unreadCount']);
        Route::patch('/notifications/read-all', [InstructorNotificationController::class, 'readAll']);
        Route::get('/notifications', [InstructorNotificationController::class, 'index']);
        Route::get('/notifications/{id}', [InstructorNotificationController::class, 'show'])->whereNumber('id');
        Route::patch('/notifications/{id}/read', [InstructorNotificationController::class, 'read'])->whereNumber('id');
        Route::delete('/notifications/{id}', [InstructorNotificationController::class, 'destroy'])->whereNumber('id');
        /*
|--------------------------------------------------------------------------
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
*/
        Route::match(['get', 'post'], '/quizzes', [InstructorCourseController::class, 'manageQuizzes']);
        Route::match(['get', 'put', 'patch', 'delete'], '/quizzes/{id}', [InstructorCourseController::class, 'manageQuizzes'])
            ->whereNumber('id');
    });
/*
|--------------------------------------------------------------------------
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner'])
    ->group(function (): void {
        Route::get('/me/instructor-upgrade', [InstructorUpgradeController::class, 'myApplication']);
        Route::post('/me/instructor-upgrade', [InstructorUpgradeController::class, 'store']);
        Route::put('/me/instructor-upgrade', [InstructorUpgradeController::class, 'update']);
    });
/*
|--------------------------------------------------------------------------
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
*/
Route::middleware(['auth.session', 'active.user', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        /*
|--------------------------------------------------------------------------
| Ghi chú
|--------------------------------------------------------------------------
| Nội dung mô tả cũ bị lỗi mã hóa, đã được chuẩn hóa lại.
*/
        Route::get('/dashboard', [ReportController::class, 'instructorDashboard'])
            ->name('instructor.dashboard');
        Route::get('/dashboard/revenue-chart', [ReportController::class, 'instructorRevenueChart']);
        Route::get('/dashboard/enrollment-chart', [ReportController::class, 'instructorEnrollmentChart']);
        Route::get('/dashboard/top-courses', [ReportController::class, 'instructorTopCourses']);
        Route::get('/dashboard/incomplete-courses', [ReportController::class, 'incompleteCourses']);

        /*
        |--------------------------------------------------------------------------
        | Revenues & Reports routes
        |--------------------------------------------------------------------------
        */
        Route::get('/revenues/summary', [ReportController::class, 'revenueSummary']);
        Route::get('/revenues/chart', [ReportController::class, 'instructorRevenueChart']);
        Route::get('/revenues/enrollment-chart', [ReportController::class, 'instructorEnrollmentChart']);
        Route::get('/revenues/top-courses', [ReportController::class, 'topCoursesByRevenue']);
        Route::get('/revenues/course-breakdown', [ReportController::class, 'courseBreakdown']);

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





        Route::get('/dashboard/alerts', [ReportController::class, 'instructorDashboardAlerts'])
            ->name('instructor.dashboard.alerts');
    });
