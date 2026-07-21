<?php
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'active.user', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        Route::post('/course-announcements', [MarketingController::class, 'courseAnnouncements']);
    });