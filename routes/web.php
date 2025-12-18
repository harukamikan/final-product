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
use App\Http\Controllers\QiitaArticleController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\GoalUploadController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\MileHistoryController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/auth/slack/redirect', [SlackAuthController::class, 'redirect'])->name('slack.login');
Route::get('/auth/slack/callback', [SlackAuthController::class, 'callback'])->name('slack.callback');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/profile/delete/confirm', [ProfileController::class, 'confirmDelete'])
        ->name('profile.delete.confirm');

    Route::delete('/profile/delete', [ProfileController::class, 'destroy'])
        ->name('profile.delete');


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
        ->middleware('auth')
        ->name('ranking.index');
    Route::get('/missions/google-form', [MissionController::class, 'showGoogleForm'])
        ->name('missions.google_form.form');
    Route::post('/missions/google-form', [MissionController::class, 'storeGoogleForm'])
        ->name('missions.google_form.store');
    Route::middleware(['auth']) // 管理者限定にする場合は、ここに管理者ミドルウェアを追加
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('missions', MissionCreater::class);
        });
    Route::post('/missions/{mission}/complete', [UserMissionController::class, 'complete'])
        ->name('missions.complete');
    Route::get('/missions', [MissionListController::class, 'index'])->name('missions.index');
    Route::get('/missions/completed', [MileHistoryController::class, 'index'])
        ->middleware('auth')
        ->name('missions.completed');
    Route::get('/qiita', [QiitaArticleController::class, 'index'])
        ->name('qiita.index');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
});


// Goal routes
Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
Route::get('/goals/create', [GoalController::class, 'create'])->name('goals.create');
Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
Route::get('/goals/{id}/edit', [GoalController::class, 'edit'])->name('goals.edit');
Route::put('/goals/{id}', [GoalController::class, 'update'])->name('goals.update');

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth');

require __DIR__ . '/auth.php';

Route::get('/debug/users', function () {
    $users = \App\Models\User::all(['id', 'name', 'email', 'slack_id']);
    return response()->json($users);
})->middleware('auth');

// 管理者用ルート
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/goals/upload', [GoalUploadController::class, 'index'])->name('admin.goals.upload.index');
    Route::post('/goals/upload', [GoalUploadController::class, 'upload'])->name('admin.goals.upload');
});

// AI自動抽出
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/goals/ai-upload', [App\Http\Controllers\Admin\GoalAiUploadController::class, 'index'])->name('admin.goals.ai.index');
    Route::post('/goals/ai-upload', [App\Http\Controllers\Admin\GoalAiUploadController::class, 'upload'])->name('admin.goals.ai.upload');
    Route::get('/goals/ai-confirm', [App\Http\Controllers\Admin\GoalAiUploadController::class, 'confirm'])->name('admin.goals.ai.confirm');
    Route::post('/goals/ai-store', [App\Http\Controllers\Admin\GoalAiUploadController::class, 'store'])->name('admin.goals.ai.store');
});
