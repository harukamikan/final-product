<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\{
    ProfileController,
    GoalController,
    MissionController,
    DashboardController,
    SlackAuthController,
    UserMissionController,
    MissionListController,
    QiitaArticleController,
    RankingController,
    StatsController,
    MileHistoryController,
    SemesterGoalController,
    ActivityController
};

use App\Http\Controllers\Admin\{
    MissionCreater,
    GoalUploadController,
    GoalAiUploadController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/auth/slack/redirect', [SlackAuthController::class, 'redirect'])
    ->name('slack.login');

Route::get('/auth/slack/callback', [SlackAuthController::class, 'callback'])
    ->name('slack.callback');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    | Dashboard
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    | Semester Goals（半期目標）
    */
    Route::resource('semester-goals', SemesterGoalController::class)
        ->only(['create', 'store', 'edit', 'update']);

    /*
    | Profile
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::get('/profile/delete/confirm', [ProfileController::class, 'confirmDelete'])
        ->name('profile.delete.confirm');

    Route::delete('/profile/delete', [ProfileController::class, 'destroy'])
        ->name('profile.delete');

    /*
    | Goals（通常の活動・目標）
    */
    Route::resource('goals', GoalController::class)
        ->except(['show']);

    /*
    | Missions
    */
    Route::get('/missions', [MissionListController::class, 'index'])
        ->name('missions.index');

    Route::post('/missions/{mission}/complete', [UserMissionController::class, 'complete'])
        ->name('missions.complete');

    Route::get('/missions/completed', [MileHistoryController::class, 'index'])
        ->name('missions.completed');

    Route::get('/missions/blog-url', [MissionController::class, 'showBlogUrlForm'])
        ->name('missions.blog-url.form');

    Route::post('/missions/blog-url', [MissionController::class, 'submitBlogUrl'])
        ->name('missions.blog-url.submit');

    Route::get('/missions/google-form', [MissionController::class, 'showGoogleForm'])
        ->name('missions.google_form.form');

    Route::post('/missions/google-form', [MissionController::class, 'storeGoogleForm'])
        ->name('missions.google_form.store');

    Route::get('/missions/{mission}', [MissionController::class, 'show'])
        ->name('missions.show');

    /*
    | Activities
    */
    Route::get('/activities', [ActivityController::class, 'index'])
        ->name('activities.index');


    /*
    | Ranking / Stats
    */
    Route::get('/ranking', [RankingController::class, 'index'])
        ->name('ranking.index');

    Route::get('/stats', [StatsController::class, 'index'])
        ->name('stats.index');

    /*
    | Qiita
    */
    Route::get('/qiita', [QiitaArticleController::class, 'index'])
        ->name('qiita.index');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {

        // Mission management
        Route::resource('missions', MissionCreater::class);

        // CSV Upload
        Route::get('/goals/upload', [GoalUploadController::class, 'index'])
            ->name('goals.upload.index');

        Route::post('/goals/upload', [GoalUploadController::class, 'upload'])
            ->name('goals.upload');

        // AI Upload
        Route::get('/goals/ai-upload', [GoalAiUploadController::class, 'index'])
            ->name('goals.ai.index');

        Route::post('/goals/ai-upload', [GoalAiUploadController::class, 'upload'])
            ->name('goals.ai.upload');

        Route::get('/goals/ai-confirm', [GoalAiUploadController::class, 'confirm'])
            ->name('goals.ai.confirm');

        Route::post('/goals/ai-store', [GoalAiUploadController::class, 'store'])
            ->name('goals.ai.store');
    });
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Auth (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Debug (開発用)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/debug/users', function () {
    return response()->json(
        \App\Models\User::all(['id', 'name', 'email', 'slack_id'])
    );
});
