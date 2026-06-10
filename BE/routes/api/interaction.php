<?php

use App\Http\Controllers\InteractionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'role:learner'])
    ->group(function (): void {
        Route::match(['get', 'post'], '/lessons/{id}/comments', [InteractionController::class, 'lessonComments'])
            ->where('id', '[0-9]+');
    });

Route::middleware(['auth.session', 'role:instructor'])
    ->group(function (): void {
        Route::post('/comments/{id}/replies', [InteractionController::class, 'replyComment'])
            ->where('id', '[0-9]+');
    });

