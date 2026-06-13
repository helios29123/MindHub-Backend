<?php
use App\Http\Controllers\InstructorCourseController;
use Illuminate\Support\Facades\Route;
Route::middleware(["auth.session", "role:instructor"])
    ->prefix("instructor")
    ->group(function (): void {
        Route::post("/courses", [InstructorCourseController::class, "store"]);
        Route::get("/lessons", [
            InstructorCourseController::class,
            "indexLessons",
        ]);
        Route::post("/lessons", [
            InstructorCourseController::class,
            "storeLesson",
        ]);
        Route::get("/lessons/{id}", [
            InstructorCourseController::class,
            "showLesson",
        ])->whereNumber("id");
        Route::patch("/lessons/{id}/preview", [
            InstructorCourseController::class,
            "togglePreview",
        ])->whereNumber("id");
        Route::match(["put", "patch"], "/lessons/{id}", [
            InstructorCourseController::class,
            "updateLesson",
        ])->whereNumber("id");
        Route::delete("/lessons/{id}", [
            InstructorCourseController::class,
            "destroyLesson",
        ])->whereNumber("id");
        Route::post("/lessons/{id}/video", [
            InstructorCourseController::class,
            "uploadVideo",
        ])->whereNumber("id");
        Route::post("/lessons/{id}/assets", [
            InstructorCourseController::class,
            "uploadAsset",
        ])->whereNumber("id");
        Route::patch("/courses/{id}", [
            InstructorCourseController::class,
            "update",
        ])->where("id", "[0-9]+");

        Route::get("/profile", [
            InstructorCourseController::class,
            "profile",
        ]);

        Route::patch("/profile", [
            InstructorCourseController::class,
            "updateProfile",
        ]);
        Route::get("/sections", [
            InstructorCourseController::class,
            "sections",
        ]);
        Route::post("/sections", [
            InstructorCourseController::class,
            "storeSection",
        ]);

        Route::get("/sections/{id}", [
            InstructorCourseController::class,
            "showSection",
        ])->where("id", "[0-9]+");

        Route::put("/sections/{id}", [
            InstructorCourseController::class,
            "updateSection",
        ])->where("id", "[0-9]+");

        Route::patch("/sections/{id}", [
            InstructorCourseController::class,
            "updateSection",
        ])->where("id", "[0-9]+");

        Route::delete("/sections/{id}", [
            InstructorCourseController::class,
            "deleteSection",
        ])->where("id", "[0-9]+");
    });

Route::middleware(["auth.session", "active.user", "role:instructor"])
    ->post("/instructor/courses/{id}/submit", [
        \App\Http\Controllers\InstructorCourseController::class,
        "submitForReview",
    ])
    ->whereNumber("id");
Route::middleware(["auth.session", "active.user", "role:instructor"])->get(
    "/instructor/courses/{id}/review-notes",
    [\App\Http\Controllers\InstructorCourseController::class, "reviewNotes"],
);
