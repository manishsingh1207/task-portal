<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    // Restrict creation to Admin or Manager
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('role:Admin|Manager')->name('tasks.store');
    // Update authorization handled by policy, keep route protected by auth+verified
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
});

require __DIR__ . '/auth.php';
