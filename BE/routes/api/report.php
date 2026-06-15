<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
Route::middleware('auth:sanctum')->prefix('instructor/reports')->group(function () {
    Route::get('/completion-rate', [ReportController::class, 'completionRate']);
});