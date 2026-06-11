<?php

use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'role:learner'])
    ->group(function (): void {
        Route::post('/quizzes/{id}/attempts', [QuizController::class, 'storeAttempt'])
            ->where('id', '[0-9]+');
    });
