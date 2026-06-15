<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
Route::middleware('auth:sanctum')->prefix('instructor/reports')->group(function () {
    Route::get('/completion-rate', [ReportController::class, 'completionRate']);
});

Route::middleware(['auth.session', 'role:admin'])
    ->prefix('admin/reports')
    ->group(function (): void {
        Route::get('/top-courses', [ReportController::class, 'topCourses']);
        Route::get('/instructors', [ReportController::class, 'topInstructors']);
    });