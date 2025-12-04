<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoalController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/goals/create', [GoalController::class, 'create'])->name('goals.create');
Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
