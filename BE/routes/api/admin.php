<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminModerationController;
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::middleware(["auth.session", "role:admin"])
    ->prefix("admin")
    ->group(function (): void {
        Route::patch("/moderation/items/{id}", [
            AdminModerationController::class,
            "moderateItem",
        ])->where("id", "[0-9]+");

        Route::match(["get", "post"], "/campaigns", [
            \App\Http\Controllers\MarketingController::class,
            "banners",
        ]);
        Route::match(["get", "put", "patch", "delete"], "/campaigns/{id}", [
            \App\Http\Controllers\MarketingController::class,
            "banners",
        ])->where("id", "[0-9]+");

        Route::match(["get", "post"], "/banners", [
            \App\Http\Controllers\AdminController::class,
            "banners",
        ]);
        Route::match(["get", "put", "patch", "delete"], "/banners/{id}", [
            \App\Http\Controllers\AdminController::class,
            "banners",
        ])->where("id", "[0-9]+");

        Route::match(["get", "post"], "/roles", [
            AdminController::class,
            "roles",
        ]);
        Route::match(["get", "put", "patch", "delete"], "/roles/{id}", [
            AdminController::class,
            "roles",
        ])->where("id", "[0-9]+");
    });
