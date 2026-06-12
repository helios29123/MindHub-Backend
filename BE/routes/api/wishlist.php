<?php
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'role:learner'])
    ->prefix('wishlists')
    ->group(function (): void {
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{courseId}', [WishlistController::class, 'destroy']);
    });