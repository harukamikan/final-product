<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MileHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StatsController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // 最古のデータの月を取得
        $oldestMonth = MileHistory::where('user_id', $userId)
            ->orderBy('created_at')
            ->value(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));

        // データがない場合は現在月から
        $startMonth = $oldestMonth ? $oldestMonth : now()->format('Y-m');

        // 月ラベルを作成（最古の月〜来月まで）
        $months = collect();
        $current = Carbon::createFromFormat('Y-m', $startMonth);
        $end = now();

        while ($current <= $end) {
            $months->push($current->format('Y-m'));
            $current->addMonth();
        }

        // 月別マイル獲得
        $monthlyMilesData = MileHistory::where('user_id', $userId)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(miles) as total')
            )
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month')
            ->pluck('total', 'month');

        // 全ての月にデータを埋める
        $monthlyMiles = $months->map(function($month) use ($monthlyMilesData) {
            return [
                'month' => $month,
                'total' => $monthlyMilesData->get($month, 0)
            ];
        });

        // 累積マイル計算
        $cumulativeMiles = [];
        $total = 0;
        foreach ($monthlyMiles as $data) {
            $total += $data['total'];
            $cumulativeMiles[] = [
                'month' => $data['month'],
                'total' => $total
            ];
        }

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
            'totalActivities',
            'cumulativeMiles'
        ));
    }
}
