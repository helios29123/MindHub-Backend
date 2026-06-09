<?php

use App\Http\Controllers\CoursePublicController;
use Illuminate\Support\Facades\Route;

Route::get('/courses/{slug}', [CoursePublicController::class, 'show']);
Route::get('/courses/{id}/outline', [CoursePublicController::class, 'outline'])->where('id', '[0-9]+');
Route::get('/lessons/{id}/preview', [CoursePublicController::class, 'previewLesson'])->where('id', '[0-9]+');
Route::get('/courses/{id}/reviews', [CoursePublicController::class, 'reviews'])->where('id', '[0-9]+');
