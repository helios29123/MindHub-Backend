<?php

use App\Http\Controllers\LearningController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'active.user', 'role:learner'])->group(function (): void {
    Route::get('/me/courses', [LearningController::class, 'myCourses']);
});
