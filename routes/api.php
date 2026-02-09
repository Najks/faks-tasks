<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource("status", \App\Http\Controllers\TaskStatusController::class);
