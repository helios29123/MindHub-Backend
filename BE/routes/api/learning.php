<?php

use App\Http\Controllers\LearningController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'active.user'])->group(function (): void {
    Route::get('/learn/lessons/{id}/check-access', [LearningController::class, 'canAccessLesson'])->whereNumber('id');
});

Route::middleware(['auth.session', 'active.user', 'role:learner'])->group(function (): void {
    Route::get('/me/courses', [LearningController::class, 'myCourses']);
    Route::get('/learn/lessons/{id}', [LearningController::class, 'showLesson'])->whereNumber('id');
    Route::get('/learn/courses/{id}/outline', [LearningController::class, 'outline'])->whereNumber('id');
    Route::patch('/learn/lessons/{id}/progress', [LearningController::class, 'saveVideoProgress'])->whereNumber('id');
    Route::get('/learn/resume', [LearningController::class, 'resume']);
    Route::patch('/learn/lessons/{id}/complete', [LearningController::class, 'completeLesson'])->whereNumber('id');
    Route::get('/learn/courses/{id}/progress', [LearningController::class, 'courseProgress'])->whereNumber('id');
    Route::get('/learning-logs/my', [LearningController::class, 'learningLogs']);
    Route::get('/learn/assets/{id}/download', [LearningController::class, 'downloadAsset'])->whereNumber('id');
    Route::get('/learn/lessons/{id}/next', [LearningController::class, 'nextLesson'])->whereNumber('id');
    Route::get('/me/recommendations/rule-based', [LearningController::class, 'ruleBasedRecommendations']);
    Route::get('/me/learning-path/next', [LearningController::class, 'nextLearningPath']);
    Route::get('/me/dynamic-alerts', [LearningController::class, 'dynamicAlerts']);
    Route::post('/learn/assets/{assetId}/signed-url', [LearningController::class, 'signedAssetUrl'])->where('assetId', '[0-9]+');
    Route::get('/learn/lessons/{lessonId}/watermark-info', [LearningController::class, 'watermarkInfo'])->where('lessonId', '[0-9]+');
});
