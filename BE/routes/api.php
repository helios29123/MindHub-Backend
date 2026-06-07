<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(base_path('routes/api/auth.php'));

// Sau này làm module nào thì mở thêm dòng tương ứng
// Route::prefix('user')->middleware('auth:sanctum')->group(base_path('routes/api/user.php'));
// Route::prefix('catalog')->group(base_path('routes/api/catalog.php'));
// Route::prefix('courses')->group(base_path('routes/api/course.php'));
// Route::prefix('instructor')->middleware(['auth:sanctum'])->group(base_path('routes/api/instructor.php'));
// Route::prefix('admin')->middleware(['auth:sanctum'])->group(base_path('routes/api/admin.php'));
// Route::prefix('wishlist')->middleware('auth:sanctum')->group(base_path('routes/api/wishlist.php'));
// Route::prefix('payment')->middleware('auth:sanctum')->group(base_path('routes/api/payment.php'));
// Route::prefix('learning')->middleware('auth:sanctum')->group(base_path('routes/api/learning.php'));
