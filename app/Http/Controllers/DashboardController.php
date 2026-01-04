<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\MileHistory;
use App\Models\UserMission;
use App\Models\Mission;
use App\Models\SemesterGoal;
use App\Models\RewardSurvey;
use App\Models\RewardDistribution;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        $company = $user->company;

        // 初期値（会社未所属でも落ちない）
        $showRewardSurveyNotice = false;

        // 会社に所属している場合のみ判定
        if ($company) {
            $hasAnsweredSurvey = RewardSurvey::where('user_id', $userId)
                ->where('company_id', $company->id)
                ->exists();

            $showRewardSurveyNotice =
                $company->reward_survey_active
                && ! $hasAnsweredSurvey
                && (
                    !$company->reward_survey_deadline
                    || now()->lte($company->reward_survey_deadline)
                );
        }

        // 最近の目標（3件）
        $recentGoals = Goal::where('user_id', $userId)
            ->latest()
            ->take(3)
            ->get();

        // 今月の活動数
        $thisMonthGoals = Goal::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->count();

        // 総マイル数
        $totalMiles = MileHistory::where('user_id', $userId)
            ->sum('miles');

        // 今月のマイル数
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

        // 今期の半期目標
        $semesterGoal = SemesterGoal::where('user_id', $userId)
            ->where('is_current', true)
            ->first();

        // 最近達成したミッション（3件）
        $recentMissions = UserMission::where('user_id', $userId)
            ->with('mission')
            ->latest()
            ->take(3)
            ->get();

        // ランク判定
        $rank = $this->calculateRank($totalMiles);

        //　ガチャが引けるかどうか
        $canDrawGacha = RewardDistribution::where('company_id', $userId = Auth::user()->company_id)
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

        // おすすめミッション
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
            'weeklyData'
        ));
    }

    private function calculateRank($miles)
    {
        if ($miles >= 500) return 'ゴールド';
        if ($miles >= 200) return 'シルバー';
        return 'ブロンズ';
    }
}
