<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\{
    ProfileController,
    GoalController,
    MissionController,
    DashboardController,
    SlackAuthController,
    SlackController,
    UserMissionController,
    MissionListController,
    QiitaArticleController,
    RankingController,
    StatsController,
    MileHistoryController,
    SemesterGoalController,
    ActivityController,
    CompanyController,
    MissionFormController,
    GachaController,
    InviteController,
    RewardPlayController,
    RewardHistoryController,
    DebugController,
    RewardSurveyController,
    UserRewardController,
    PersonalMissionController,
};

use App\Http\Controllers\Admin\{
    MissionCreater,
    GoalUploadController,
    GoalAiUploadController,
    AdminDashboardController,
    AdminRewardController,
    RewardDistributionController,
};
use App\Models\RewardHistory;

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

Route::get('/invite/{token}', [InviteController::class, 'accept'])
    ->name('invite.accept');

// Slack mission form routes (public, token-based authentication)
Route::get('/slack/missions/{type}', [SlackController::class, 'showSlackForm'])
    ->name('slack.missions.form');
Route::post('/slack/missions/qiita/submit', [SlackController::class, 'submitSlackQiitaForm'])
    ->name('slack.missions.qiita.submit');
Route::post('/slack/missions/{type}/submit', [SlackController::class, 'submitSlackForm'])
    ->name('slack.missions.submit');

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

    // Onboarding survey (after company selection, before dashboard)
    Route::get('/onboarding/survey', [\App\Http\Controllers\OnboardingController::class, 'showSurvey'])
        ->name('onboarding.survey');

    Route::post('/onboarding/survey', [\App\Http\Controllers\OnboardingController::class, 'submitSurvey'])
        ->name('onboarding.submit');

    // ===== 表示画面（GET）=====
    Route::get('/rewards/gacha', [RewardPlayController::class, 'gachaPage'])
        ->name('rewards.gacha');

    Route::get('/rewards/scratch', [RewardPlayController::class, 'scratchPage'])
        ->name('rewards.scratch');

    // ===== 実行処理（POST）=====
    Route::post('/rewards/play/gacha', [RewardPlayController::class, 'gacha'])
        ->name('rewards.play.gacha');

    Route::post('/rewards/play/scratch', [RewardPlayController::class, 'scratch'])
        ->name('rewards.play.scratch');

    // ===== 所有報酬一覧 =====
    Route::get('/rewards/my', [UserRewardController::class, 'index'])
        ->name('rewards.my');
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
    | Notifications
    */
    Route::post('/notifications/mark-all-read', function () {
        \App\Models\Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['success' => true]);
    })->name('notifications.mark-all-read');

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

    Route::post('/profile/regenerate-invite', [ProfileController::class, 'regenerateInviteLink'])
        ->name('profile.regenerate-invite');

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
    // 個人ミッション管理（作成・編集・削除）
    Route::get('/missions/personal/create', [PersonalMissionController::class, 'create'])
        ->name('personal-missions.create');
    Route::post('/missions/personal', [PersonalMissionController::class, 'store'])
        ->name('personal-missions.store');
    Route::get('/missions/personal/{mission}/edit', [PersonalMissionController::class, 'edit'])
        ->name('personal-missions.edit');
    Route::patch('/missions/personal/{mission}', [PersonalMissionController::class, 'update'])
        ->name('personal-missions.update');
    Route::delete('/missions/personal/{mission}', [PersonalMissionController::class, 'destroy'])
        ->name('personal-missions.destroy');

    /*
    | Qiita
    */
    Route::get('/qiita', [QiitaArticleController::class, 'index'])
        ->name('qiita.index');

    /*
    | 報酬履歴
    */
    Route::get('/rewards/history', [RewardHistoryController::class, 'index'])
        ->name('rewards.history');

    /*
    |--------------------------------------------------------------------------
    | Reward Survey（社員用）
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'company'])->group(function () {

        // アンケート回答フォーム表示
        Route::get(
            '/reward-survey/{rewardSurvey}',
            [RewardSurveyController::class, 'create']
        )->name('reward-survey.create');

        // アンケート回答保存
        Route::post(
            '/reward-survey/{rewardSurvey}',
            [RewardSurveyController::class, 'store']
        )->name('reward-survey.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes（まずは company 内。あとで admin middleware を追加）
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/rewards/toggle', [AdminRewardController::class, 'toggle'])
            ->name('rewards.toggle');

        Route::post('/rewards/deadline', [AdminRewardController::class, 'setDeadline'])
            ->name('rewards.deadline');

        //報酬配布管理
        Route::get(
            '/reward-distributions',
            [RewardDistributionController::class, 'index']
        )->name('reward-distributions.index');

        Route::post(
            '/reward-distributions',
            [RewardDistributionController::class, 'store']
        )->name('reward-distributions.store');

        Route::patch(
            '/reward-distributions/{distribution}/toggle',
            [RewardDistributionController::class, 'toggle']
        )->name('reward-distributions.toggle');

        Route::post(
            '/rewards/decide',
            [AdminRewardController::class, 'decide']
        )->name('rewards.decide');

        Route::post('/rewards/bulk-decide', [AdminRewardController::class, 'bulkDecide'])
            ->name('rewards.bulk-decide');

        Route::delete(
            '/reward-distributions/{distribution}',
            [RewardDistributionController::class, 'destroy']
        )->name('reward-distributions.destroy');

        // Mission management
        Route::resource('missions', MissionCreater::class);

        Route::get('/rewards', [AdminRewardController::class, 'index'])
            ->name('rewards.index');

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

        Route::get('/settings', [App\Http\Controllers\Admin\AdminSettingController::class, 'index'])
            ->name('settings.index');
        Route::post('/settings', [App\Http\Controllers\Admin\AdminSettingController::class, 'store'])
            ->name('settings.store');
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
Route::middleware(['auth', 'company'])
    ->prefix('admin/debug')
    ->group(function () {

        // ユーザー一覧
        Route::get('/users', function () {
            return response()->json(
                \App\Models\User::all(['id', 'name', 'email', 'slack_id', 'company_id'])
            );
        });

        // 🧪 テスト用マイル付与
        Route::post('/add-miles', [DebugController::class, 'addMiles'])
            ->name('debug.add-miles');

        Route::post('/add-scratch-points', [DebugController::class, 'addScratchPoints'])
            ->name('debug.add-scratch-points');
    });
