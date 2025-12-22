<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SlackAuthController;
use App\Http\Controllers\Admin\MissionCreater;
use App\Http\Controllers\UserMissionController;
use App\Http\Controllers\MissionListController;
use App\Http\Controllers\QiitaArticleController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\CompanyController;

// ★ company/join を作るならここで読み込む（後で追加）
// use App\Http\Controllers\CompanyController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Slack OAuth（ログイン前でも必要なら company 外）
Route::get('/auth/slack/redirect', [SlackAuthController::class, 'redirect'])->name('slack.login');
Route::get('/auth/slack/callback', [SlackAuthController::class, 'callback'])->name('slack.callback');

Route::middleware('auth')->group(function () {
    // 会社作成
    Route::get('/company/create', [CompanyController::class, 'showCreateForm'])->name('company.create');
    Route::post('/company/create', [CompanyController::class, 'store'])->name('company.store');

    // 会社参加（招待コード）
    Route::get('/company/join', [CompanyController::class, 'showJoinForm'])->name('company.join');
    Route::post('/company/join', [CompanyController::class, 'join'])->name('company.join.submit');
});
/**
 * ★ 会社参加（招待コード入力など）
 * EnsureCompanySelected が company.join に飛ばす設計なら、ここは company ミドルウェア外に置く
 */
// Route::middleware('auth')->group(function () {
//     Route::get('/company/join', [CompanyController::class, 'showJoinForm'])->name('company.join');
//     Route::post('/company/join', [CompanyController::class, 'join'])->name('company.join.submit');
// });

/**
 * 会社所属が必要な機能は全部ここに集約（クローズド化）
 */
Route::middleware(['auth', 'company'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['verified'])
        ->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/profile/delete/confirm', [ProfileController::class, 'confirmDelete'])
        ->name('profile.delete.confirm');
    Route::delete('/profile/delete', [ProfileController::class, 'destroy'])
        ->name('profile.delete');

    // Missions
    Route::get('/missions', [MissionListController::class, 'index'])->name('missions.index');
    Route::get('/missions/completed', [MissionListController::class, 'completed'])->name('missions.completed');

    Route::get('/missions/blog-url', [MissionController::class, 'showBlogUrlForm'])
        ->name('missions.blog-url.form');
    Route::post('/missions/blog-url', [MissionController::class, 'submitBlogUrl'])
        ->name('missions.blog-url.submit');

    Route::get('/missions/google-form', [MissionController::class, 'showGoogleForm'])
        ->name('missions.google_form.form');
    Route::post('/missions/google-form', [MissionController::class, 'storeGoogleForm'])
        ->name('missions.google_form.store');

    Route::post('/missions/{mission}/complete', [UserMissionController::class, 'complete'])
        ->name('missions.complete');

    // Qiita Timeline（社内限定）
    Route::get('/qiita', [QiitaArticleController::class, 'index'])
        ->name('qiita.index');

    // Ranking（社内限定）
    Route::get('/ranking', [RankingController::class, 'index'])
        ->name('ranking.index');

    // Goals（社内限定）
    Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
    Route::get('/goals/create', [GoalController::class, 'create'])->name('goals.create');
    Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
    Route::get('/goals/{id}/edit', [GoalController::class, 'edit'])->name('goals.edit');
    Route::put('/goals/{id}', [GoalController::class, 'update'])->name('goals.update');

    // Admin（社内 + 権限は後で admin middleware を追加）
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('missions', MissionCreater::class);
    });

    // Debug（社内限定にするならここでOK）
    Route::get('/debug/users', function () {
        $users = \App\Models\User::all(['id', 'name', 'email', 'slack_id']);
        return response()->json($users);
    })->name('debug.users');
});

// logout（GETログアウトはCSRF的に推奨されないけど、今のままなら一旦維持）
Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth');

require __DIR__ . '/auth.php';
