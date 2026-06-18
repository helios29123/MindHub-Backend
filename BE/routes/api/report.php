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