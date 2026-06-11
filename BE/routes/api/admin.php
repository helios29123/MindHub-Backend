<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminModerationController;
use Illuminate\Support\Facades\Route;

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
        Route::get("/users", [AdminController::class, "users"]);
        Route::get("/users/{id}", [AdminController::class, "showUser"])->where(
            "id",
            "[0-9]+",
        );
        Route::post("/users", [AdminController::class, "storeUser"]);
        Route::put("/users/{id}", [
            AdminController::class,
            "updateUser",
        ])->where("id", "[0-9]+");
        Route::patch("/users/{id}", [
            AdminController::class,
            "updateUser",
        ])->where("id", "[0-9]+");
        Route::delete("/users/{id}", [
            AdminController::class,
            "deleteUser",
        ])->where("id", "[0-9]+");
    });
