<?php

use App\Http\Controllers\InteractionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'role:learner'])
    ->group(function (): void {
        Route::match(['get', 'post'], '/lessons/{id}/comments', [InteractionController::class, 'lessonComments'])
            ->where('id', '[0-9]+');
    });
