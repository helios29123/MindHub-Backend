<?php

use App\Http\Controllers\CoursePublicController;
use Illuminate\Support\Facades\Route;

Route::get('/courses/{slug}', [CoursePublicController::class, 'show']);
