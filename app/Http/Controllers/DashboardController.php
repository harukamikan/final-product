<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\MileHistory;
use App\Models\UserMission;
use App\Models\Mission;
use App\Models\SemesterGoal;
use App\Models\RewardSurvey;
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
                $company->reward_survey_active && ! $hasAnsweredSurvey;
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
            'showRewardSurveyNotice'
        ));
    }

    private function calculateRank($miles)
    {
        if ($miles >= 500) return 'ゴールド';
        if ($miles >= 200) return 'シルバー';
        return 'ブロンズ';
    }
}
