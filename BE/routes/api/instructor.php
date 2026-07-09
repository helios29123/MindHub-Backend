<?php
use App\Http\Controllers\InstructorCourseController;
use App\Http\Controllers\InstructorCreditController;
use App\Http\Controllers\InstructorUpgradeController;
use App\Http\Controllers\InteractionController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Instructor routes
|--------------------------------------------------------------------------
| Tất cả route dành cho instructor gom chung vào 1 group:
| /api/instructor/...
|
| Có active.user để chặn instructor inactive/locked thao tác.
*/
Route::middleware(['auth.session', 'active.user', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | Credit packages / course credits
        |--------------------------------------------------------------------------
        | Giảng viên xem gói lượt, số dư lượt, lịch sử lượt và tạo đơn mua gói lượt.
        */
        Route::get('/credit-packages', [InstructorCreditController::class, 'packages']);
        Route::get('/course-credits', [InstructorCreditController::class, 'balance']);
        Route::get('/credit-transactions', [InstructorCreditController::class, 'transactions']);
        Route::post('/credit-orders', [InstructorCreditController::class, 'createOrder']);
        /*
        |--------------------------------------------------------------------------
        | Courses
        |--------------------------------------------------------------------------
        */
        Route::post('/courses', [InstructorCourseController::class, 'store']);
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
        | Lessons
        |--------------------------------------------------------------------------
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
        | Sections
        |--------------------------------------------------------------------------
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
        | Profile
        |--------------------------------------------------------------------------
        */
        Route::get('/profile', [InstructorCourseController::class, 'profile']);
        Route::patch('/profile', [InstructorCourseController::class, 'updateProfile']);
        /*
        |--------------------------------------------------------------------------
        | Revenue / withdrawals
        |--------------------------------------------------------------------------
        */
        Route::get('/revenue', [InstructorCourseController::class, 'revenue']);
        Route::post('/withdrawals', [InstructorCourseController::class, 'withdraw']);
        /*
        |--------------------------------------------------------------------------
        | Quizzes
        |--------------------------------------------------------------------------
        */
        Route::match(['get', 'post'], '/quizzes', [InstructorCourseController::class, 'manageQuizzes']);
        Route::match(['get', 'put', 'patch', 'delete'], '/quizzes/{id}', [InstructorCourseController::class, 'manageQuizzes'])
            ->whereNumber('id');
    });
/*
|--------------------------------------------------------------------------
| Learner instructor upgrade routes
|--------------------------------------------------------------------------
| Learner gửi yêu cầu nâng cấp lên instructor.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner'])
    ->group(function (): void {
        Route::get('/me/instructor-upgrade', [InstructorUpgradeController::class, 'myApplication']);
        Route::post('/me/instructor-upgrade', [InstructorUpgradeController::class, 'store']);
        Route::put('/me/instructor-upgrade', [InstructorUpgradeController::class, 'update']);
    });