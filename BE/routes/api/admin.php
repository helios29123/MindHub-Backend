<?php

use App\Http\Controllers\AdminModerationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        Route::patch('/moderation/items/{id}', [AdminModerationController::class, 'moderateItem'])
            ->where('id', '[0-9]+');
    });
