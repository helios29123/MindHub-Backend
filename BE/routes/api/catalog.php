<?php

use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [CatalogController::class, 'home']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/courses', [CatalogController::class, 'searchCourses']);
Route::get('/courses/sort', [CatalogController::class, 'sortCourses']);
Route::get('/courses/featured', [CatalogController::class, 'featuredCourses']);
Route::get('/courses/latest', [CatalogController::class, 'latestCourses']);
Route::get('/instructors', [CatalogController::class, 'featuredInstructors']);
Route::get('/instructors/featured', [CatalogController::class, 'featuredInstructors']);
Route::get('/search/suggestions', [CatalogController::class, 'searchSuggestions']);

// AI Vector Semantic Search & Analytics
Route::get('/search/semantic', [\App\Http\Controllers\SemanticSearchController::class, 'search']);
Route::get('/search/trending', [\App\Http\Controllers\SemanticSearchController::class, 'trending']);
Route::post('/search/click', [\App\Http\Controllers\SemanticSearchController::class, 'recordClick']);
Route::get('/courses/{id}/semantic-recommendations', [\App\Http\Controllers\SemanticSearchController::class, 'similarCourses']);

// Trigger seed 10 courses & 200 Bunny CDN videos
Route::match(['get', 'post'], '/courses/seed-all', function () {
    \Illuminate\Support\Facades\Artisan::call('courses:seed-all');
    return response()->json([
        'success' => true,
        'message' => 'Đã nạp thành công 10 khóa học & 200 video bài giảng Bunny Stream CDN!',
        'output' => \Illuminate\Support\Facades\Artisan::output()
    ]);
});
