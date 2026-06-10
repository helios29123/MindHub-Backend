<?php
use App\Http\Controllers\InstructorCourseController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        Route::post('/courses', [InstructorCourseController::class, 'store']);
        Route::post('/lessons/{id}/video', [InstructorCourseController::class, 'uploadVideo']);
    });
