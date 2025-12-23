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
    ActivityController,
    CompanyController,
    MissionFormController
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
| Authenticated but NOT yet in a company
| （company middleware の外に置くのが重要）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // 会社作成（未所属ユーザーが最初に行ける場所）
    Route::get('/company/create', [CompanyController::class, 'showCreateForm'])
        ->name('company.create');

    Route::post('/company/create', [CompanyController::class, 'store'])
        ->name('company.store');

    // 招待コード参加（招待された人用）
    Route::get('/company/join', [CompanyController::class, 'showJoinForm'])
        ->name('company.join');

    Route::post('/company/join', [CompanyController::class, 'join'])
        ->name('company.join.submit');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Company Scoped)
| （ここから下は “社内クローズド”）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'company'])->group(function () {

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

    // NOTE: 「完了したミッション」なら controller 名が MileHistoryController なのは意図通り？
    // もし MissionListController@completed なら戻した方がいい
    Route::get('/missions/completed', [MileHistoryController::class, 'index'])
        ->name('missions.completed');

    Route::get('/missions/blog-url', [MissionController::class, 'showBlogUrlForm'])
        ->name('missions.blog-url.form');

    Route::post('/missions/blog-url', [MissionController::class, 'submitBlogUrl'])
        ->name('missions.blog-url.submit');

    // アプリ内フォーム提出
    Route::get('/missions/{mission}/form', [MissionFormController::class, 'create'])
        ->name('missions.form.create');

    Route::post('/missions/{mission}/form', [MissionFormController::class, 'store'])
        ->name('missions.form.store');

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
    | Missions
    */
    Route::get('/missions', [MissionListController::class, 'index'])
        ->name('missions.index');
    
    Route::get('/missions/completed', [MissionListController::class, 'completed'])
        ->name('missions.completed');
    
    Route::get('/missions/personal', [MissionListController::class, 'personal'])
        ->name('missions.personal');

    Route::get('/missions/{mission}', [MissionController::class, 'show'])
        ->name('missions.show');

    Route::post('/missions/{mission}/complete', [UserMissionController::class, 'complete'])
        ->name('missions.complete');

    /*
    | Qiita
    */
    Route::get('/qiita', [QiitaArticleController::class, 'index'])
        ->name('qiita.index');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes（まずは company 内。あとで admin middleware を追加）
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
Route::middleware(['auth', 'company'])->get('/debug/users', function () {
    return response()->json(
        \App\Models\User::all(['id', 'name', 'email', 'slack_id', 'company_id'])
    );
});
