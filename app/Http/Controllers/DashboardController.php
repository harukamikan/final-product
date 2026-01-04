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
        | 最近の目標
        |--------------------------------------------------------------------------
        */
        $recentGoals = Goal::where('user_id', $userId)
            ->latest()
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

        // 週間活動データ（過去7日間）
        $weeklyActivity = Goal::where('user_id', $userId)
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // 7日分のラベルとデータを準備
        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $weeklyLabels[] = now()->subDays($i)->format('m/d');
            $weeklyData[] = $weeklyActivity[$date] ?? 0;
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
            'recentGoals',
            'thisMonthGoals',
            'totalMiles',
            'thisMonthMiles',
            'recentMissions',
            'rank',
            'recommendedMission',
            'semesterGoal',
            'showRewardSurveyNotice',
            'canDrawGacha',
            'weeklyLabels',
            'weeklyData',
            'pendingSurvey',
            'canDrawGacha'
        ));
    }

    private function calculateRank($miles)
    {
        if ($miles >= 500) return 'ゴールド';
        if ($miles >= 200) return 'シルバー';
        return 'ブロンズ';
    }
}
