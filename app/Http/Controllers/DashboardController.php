<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;

class DashboardController extends Controller
{
    public function index()
    {
        // ログインユーザーの目標を最新3件取得
        $recentGoals = Goal::where('user_id', auth()->id())
            ->latest()
            ->take(3)
            ->get();

        // 総目標数をカウント
        $totalGoals = Goal::where('user_id', auth()->id())->count();

        // 今月作成された目標数
        $thisMonthGoals = Goal::where('user_id', auth()->id())
            ->whereMonth('created_at', now()->month)
            ->count();

        return view('dashboard', compact('recentGoals', 'totalGoals', 'thisMonthGoals'));
    }
}