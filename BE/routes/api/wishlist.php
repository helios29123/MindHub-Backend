<?php
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'active.user', 'role:learner'])
    ->prefix('wishlists')
    ->group(function (): void {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{courseId}', [WishlistController::class, 'destroy']);
    });