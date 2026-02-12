<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource("status", \App\Http\Controllers\TaskStatusController::class);
Route::apiResource("tasks", \App\Http\Controllers\TaskController::class);
Route::apiResource("subjects", \App\Http\Controllers\SubjectController::class);

// login routes
Route::prefix("auth")->name("auth.")->group(function () {
    Route::post("login", [AuthController::class, "login"]);
    Route::post("register", [AuthController::class, "register"]);

    Route::middleware("auth:sanctum")->group(function () {
        Route::post("logout", [AuthController::class, "logout"]);
        Route::get("me", [AuthController::class, "me"]);
    });
});
