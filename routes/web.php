<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SlackAuthController;
use App\Http\Controllers\Admin\MissionCreater;
use App\Http\Controllers\UserMissionController;
use App\Http\Controllers\MissionListController;
use App\Http\Controllers\RankingController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/slack/redirect', [SlackAuthController::class, 'redirect'])->name('slack.login');
Route::get('/auth/slack/callback', [SlackAuthController::class, 'callback'])->name('slack.callback');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/missions/blog-url', [MissionController::class, 'showBlogUrlForm'])
        ->name('missions.blog-url.form');
    Route::post('/missions/blog-url', [MissionController::class, 'submitBlogUrl'])
        ->name('missions.blog-url.submit');
    Route::middleware(['auth']) // 権限まわりはプロジェクトに合わせて
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('missions', MissionCreater::class);
        });
    Route::post('/missions/{mission}/complete', [UserMissionController::class, 'complete'])
        ->name('missions.complete');
    Route::get('/missions', [MissionListController::class, 'index'])->name('missions.index');

    Route::get('/ranking', [RankingController::class, 'index'])
        ->name('ranking.index');
});


// Goal routes
Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
Route::get('/goals/create', [GoalController::class, 'create'])->name('goals.create');
Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
Route::get('/goals/{id}/edit', [GoalController::class, 'edit'])->name('goals.edit');
Route::put('/goals/{id}', [GoalController::class, 'update'])->name('goals.update');

require __DIR__ . '/auth.php';
