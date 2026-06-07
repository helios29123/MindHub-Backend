<?php

use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'role:learner,instructor,admin'])
    ->get('/users/me', [UserProfileController::class, 'me']);