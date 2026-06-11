<?php
use App\Http\Controllers\InstructorCourseController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        Route::post('/courses', [InstructorCourseController::class, 'store']);
        Route::get('/lessons', [InstructorCourseController::class, 'indexLessons']);
        Route::post('/lessons', [InstructorCourseController::class, 'storeLesson']);
        Route::get('/lessons/{id}', [InstructorCourseController::class, 'showLesson'])->whereNumber('id');
        Route::match(['put', 'patch'], '/lessons/{id}', [InstructorCourseController::class, 'updateLesson'])->whereNumber('id');
        Route::delete('/lessons/{id}', [InstructorCourseController::class, 'destroyLesson'])->whereNumber('id');
        Route::post('/lessons/{id}/video', [InstructorCourseController::class, 'uploadVideo'])->whereNumber('id');
    });

Route::middleware(['auth.session', 'active.user', 'role:instructor'])->post('/instructor/courses/{id}/submit', [\App\Http\Controllers\InstructorCourseController::class, 'submitForReview'])->whereNumber('id');
Route::middleware(['auth.session', 'active.user', 'role:instructor'])->get('/instructor/courses/{id}/review-notes', [\App\Http\Controllers\InstructorCourseController::class, 'reviewNotes']);
