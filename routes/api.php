<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'apiIndex']);
    Route::post('/tasks', [TaskController::class, 'apiStore']);
});
