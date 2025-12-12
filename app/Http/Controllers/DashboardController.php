<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\MileHistory;
use App\Models\UserMission;
use App\Models\Mission;


class DashboardController extends Controller
{
    public function index()
    {

        $userId = auth()->id();
        
        // ログインユーザーの目標を最新3件取得
        $recentGoals = Goal::where('user_id', $userId)
            ->latest()
            ->take(3)
            ->get();

        // 総目標数をカウント
        $totalGoals = Goal::where('user_id', $userId)->count();

        // 今月作成された目標数
        $thisMonthGoals = Goal::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->count();

            // 総マイル数を取得
        $totalMiles = MileHistory::where('user_id', $userId)
            ->sum('miles');

        // 今月獲得したマイル数
        $thisMonthMiles = MileHistory::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->sum('miles');

        // 最近達成したミッション（3件）
        $recentMissions = UserMission::where('user_id', $userId)
            ->with('mission')
            ->latest()
            ->take(3)
            ->get();

        // ランク判定
        $rank = $this->calculateRank($totalMiles);

        // 未達成の中で一番報酬が高いミッションを取得
        $recommendedMission = Mission::whereNotIn('id', 
            UserMission::where('user_id', $userId)->pluck('mission_id')
        )
        ->orderBy('reward_miles', 'desc')
        ->first();


        return view('dashboard', compact(
            'recentGoals', 
            'totalGoals', 
            'thisMonthGoals',
            'totalMiles',
            'thisMonthMiles',
            'recentMissions',
            'rank',
            'recommendedMission'
        ));

    }

        // ランク計算メソッド
        private function calculateRank($miles)
        {
            if ($miles >= 500) return 'ゴールド';
            if ($miles >= 200) return 'シルバー';
            return 'ブロンズ';
        }

    }
