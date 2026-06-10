<?php

use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [CatalogController::class, 'home']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/courses', [CatalogController::class, 'searchCourses']);
Route::get('/courses/sort', [CatalogController::class, 'sortCourses']);
Route::get('/courses/featured', [CatalogController::class, 'featuredCourses']);
Route::get('/courses/latest', [CatalogController::class, 'latestCourses']);
Route::get('/instructors/featured', [CatalogController::class, 'featuredInstructors']);
// Route::get('/search/suggestions', [CatalogController::class, 'suggestions']);
