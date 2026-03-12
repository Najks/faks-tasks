<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public authentication routes
|--------------------------------------------------------------------------
*/

Route::prefix("auth")->name("auth.")->group(function () {

    Route::post("login", [AuthController::class, "login"])
        ->middleware("throttle:login");

    Route::post("register", [AuthController::class, "register"])
        ->middleware("throttle:login");

    Route::post("password/request", [AuthController::class, "requestPasswordReset"])
        ->middleware("throttle:login");

    Route::post("password/reset", [AuthController::class, "resetPassword"]);
    Route::post("password/reset/simple", [AuthController::class, "resetPasswordSimple"]);
});

/*
|--------------------------------------------------------------------------
| Protected API routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    /*
    | User profile
    */
    Route::prefix("auth")->group(function () {
        Route::post("logout", [AuthController::class, "logout"]);
        Route::get("me", [AuthController::class, "me"]);
        Route::patch("me", [AuthController::class, "update"]);
    });

    /*
    | User tasks
    */
    Route::get("/tasks/user/{userId}", [TaskController::class, "allTasksFromUser"]);

    /*
    | Tasks
    */
    Route::apiResource("tasks", TaskController::class);
    Route::patch("/tasks/{task}/status", [TaskController::class, "updateStatus"]);

    /*
    | Subjects
    */
    Route::get("/subjects/mine", [SubjectController::class, "mine"]);
    Route::apiResource("subjects", SubjectController::class);

    /*
    | Task statuses
    */
    Route::apiResource("status", TaskStatusController::class);
});
