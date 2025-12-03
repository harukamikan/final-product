<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoalController;

Route::get('/', function () {
    return view('welcome');
});

// 目標編集機能（Ryouta担当）
Route::get('/goals/{id}/edit', [GoalController::class, 'edit'])->name('goals.edit');
Route::put('/goals/{id}', [GoalController::class, 'update'])->name('goals.update');

// 一覧画面へのリダイレクト用（仮）
Route::get('/goals', function() {
    return '目標一覧画面（まだ作成中）';
})->name('goals.index');
