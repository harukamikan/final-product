<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\MileHistory;
use App\Models\UserMission;
use App\Models\Mission;
use App\Models\SemesterGoal;
use App\Models\RewardSurvey;
use App\Models\RewardSurveyAnswer;
use App\Models\RewardDistribution;
use App\Models\UserReward;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        $company = $user->company;

        /*
        |--------------------------------------------------------------------------
        | 報酬アンケート通知判定
        |--------------------------------------------------------------------------
        */
        $pendingSurvey = null;
        $showRewardSurveyNotice = false;

        if ($company) {
            $pendingSurvey = RewardSurvey::where('company_id', $company->id)
                ->where('status', 'active')
                ->whereDoesntHave('answers', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->latest()
                ->first();

            $showRewardSurveyNotice = (bool) $pendingSurvey;
        }

        /*
        |--------------------------------------------------------------------------
        | 最近の活動（Goal + Mission）
        |--------------------------------------------------------------------------
        */
        //最新3件
        $recentActivities = Activity::where('user_id', $userId)
            ->orderByDesc('date')
            ->take(3)
            ->get();

        $thisMonthGoals = Goal::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | マイル
        |--------------------------------------------------------------------------
        */
        $totalMiles = MileHistory::where('user_id', $userId)->sum('miles');

        $thisMonthMiles = MileHistory::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->sum('miles');

        // 週間活動データ（過去7日間：Goal + UserMission）
        $weeklyGoals = Goal::where('user_id', $userId)
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        $weeklyMissions = UserMission::where('user_id', $userId)
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        // 7日分のラベルとデータを準備（Goal + Mission）
        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $weeklyLabels[] = now()->subDays($i)->format('m/d');
            $goalCount = $weeklyGoals[$date] ?? 0;
            $missionCount = $weeklyMissions[$date] ?? 0;
            $weeklyData[] = $goalCount + $missionCount;
        }

        /*
        |--------------------------------------------------------------------------
        | 半期目標
        |--------------------------------------------------------------------------
        */
        $semesterGoal = SemesterGoal::where('user_id', $userId)
            ->where('is_current', true)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | 最近のミッション
        |--------------------------------------------------------------------------
        */
        $recentMissions = UserMission::where('user_id', $userId)
            ->with('mission')
            ->latest()
            ->take(3)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ランク
        |--------------------------------------------------------------------------
        */
        $rank = $this->calculateRank($totalMiles);

        /*
        |--------------------------------------------------------------------------
        | ガチャ可否
        |--------------------------------------------------------------------------
        */
        $canDrawGacha = RewardDistribution::where('company_id', $company?->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('quantity')
                    ->orWhere('quantity', '>', 0);
            })
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | 期限が7日以内の報酬
        |--------------------------------------------------------------------------
        */
        $expiringRewards = UserReward::with('reward')
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->whereBetween('expires_at', [
                now(),
                now()->addDays(7),
            ])
            ->orderBy('expires_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | おすすめミッション
        |--------------------------------------------------------------------------
        */
        $recommendedMission = Mission::whereNotIn(
            'id',
            UserMission::where('user_id', $userId)->pluck('mission_id')
        )
            ->orderBy('reward_miles', 'desc')
            ->first();

        return view('dashboard', compact(
            'recentActivities',
            'thisMonthGoals',
            'totalMiles',
            'thisMonthMiles',
            'rank',
            'recommendedMission',
            'semesterGoal',
            'showRewardSurveyNotice',
            'pendingSurvey',
            'canDrawGacha',
            'weeklyLabels',
            'weeklyData',
            'expiringRewards'
        ));
    }

    private function calculateRank($miles)
    {
        if ($miles >= 500) return 'ゴールド';
        if ($miles >= 200) return 'シルバー';
        return 'ブロンズ';
    }
}
