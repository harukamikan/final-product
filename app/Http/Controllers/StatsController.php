<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MileHistory;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // 月別マイル獲得（過去6ヶ月）
        $monthlyMiles = MileHistory::where('user_id', $userId)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(miles) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month')
            ->get();

        // カテゴリ別マイル獲得
        $categoryMiles = MileHistory::where('user_id', $userId)
            ->select('type', DB::raw('SUM(miles) as total'))
            ->groupBy('type')
            ->get();

        // 総統計
        $totalMiles = MileHistory::where('user_id', $userId)->sum('miles');
        $totalActivities = MileHistory::where('user_id', $userId)->count();

        return view('stats.index', compact(
            'monthlyMiles',
            'categoryMiles',
            'totalMiles',
            'totalActivities'
        ));
    }
}
