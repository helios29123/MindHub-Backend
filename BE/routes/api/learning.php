<?php

use App\Http\Controllers\LearningController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'active.user'])->group(function (): void {
    Route::get('/learn/lessons/{id}/check-access', [LearningController::class, 'canAccessLesson'])->whereNumber('id');
});

Route::middleware(['auth.session', 'active.user', 'role:learner'])->group(function (): void {
    Route::get('/me/courses', [LearningController::class, 'myCourses']);
    Route::get('/learn/lessons/{id}', [LearningController::class, 'showLesson'])->whereNumber('id');
    Route::get('/learn/courses/{id}/outline', [LearningController::class, 'outline'])->whereNumber('id');
    Route::patch('/learn/lessons/{id}/progress', [LearningController::class, 'saveVideoProgress'])->whereNumber('id');
    Route::get('/learn/resume', [LearningController::class, 'resume']);
});
