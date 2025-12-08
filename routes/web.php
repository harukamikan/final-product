<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\MissionController;

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
    Route::get('/missions/blog-url', [MissionController::class, 'showBlogUrlForm'])
        ->name('missions.blog-url.form');
    Route::post('/missions/blog-url', [MissionController::class, 'submitBlogUrl'])
        ->name('missions.blog-url.submit');
});

// Goal routes
Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
Route::get('/goals/create', [GoalController::class, 'create'])->name('goals.create');
Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
Route::get('/goals/{id}/edit', [GoalController::class, 'edit'])->name('goals.edit');
Route::put('/goals/{id}', [GoalController::class, 'update'])->name('goals.update');

require __DIR__.'/auth.php';
