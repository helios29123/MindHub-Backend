<?php

use App\Http\Controllers\CoursePublicController;
use Illuminate\Support\Facades\Route;

Route::get('/courses/ai-prompt', [CoursePublicController::class, 'aiPrompt']);
Route::post('/courses/ai-search', [CoursePublicController::class, 'aiSearch']);
Route::get('/courses/{slug}', [CoursePublicController::class, 'show']);
Route::get('/courses/{id}/outline', [CoursePublicController::class, 'outline'])->where('id', '[0-9]+');
Route::get('/lessons/{id}/preview', [CoursePublicController::class, 'previewLesson'])->where('id', '[0-9]+');
Route::get('/courses/{id}/reviews', [CoursePublicController::class, 'reviews'])->where('id', '[0-9]+');
Route::get('/instructors/{id}', [CoursePublicController::class, 'showInstructor'])->where('id', '[0-9]+');
Route::get('/courses/{id}/faqs', [CoursePublicController::class, 'faqs'])->where('id', '[0-9]+');
Route::get('/courses/{courseId}/related', [CoursePublicController::class, 'relatedCourses'])->where('courseId', '[0-9]+');
Route::post('/courses/{courseId}/view', [CoursePublicController::class, 'recordView'])->where('courseId', '[0-9]+');




