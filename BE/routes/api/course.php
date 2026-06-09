<?php

use App\Http\Controllers\CoursePublicController;
use Illuminate\Support\Facades\Route;

Route::get('/courses/{slug}', [CoursePublicController::class, 'show']);
Route::get('/courses/{id}/outline', [CoursePublicController::class, 'outline'])->where('id', '[0-9]+');
