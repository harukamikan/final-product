<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MileHistory;
use App\Models\SemesterSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        // 全ての半期を取得（ドロップダウン用）
        $semesters = SemesterSetting::orderBy('start_date', 'desc')->get();
        
        // 選択された半期（なければ現在の半期）
        $selectedSemesterId = $request->get('semester_id');
        $currentSemester = $selectedSemesterId 
            ? SemesterSetting::find($selectedSemesterId) 
            : SemesterSetting::current();

        // 現在の半期のデータだけを取得するベースクエリ
        $baseQuery = MileHistory::where('user_id', $userId)
            ->where('semester_id', $currentSemester->id);

        // 最古のデータの月を取得（現在の半期内で）
        $oldestMonth = (clone $baseQuery)
            ->orderBy('created_at')
            ->value(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));

        // データがない場合は半期開始月から
        $startMonth = $oldestMonth ? $oldestMonth : $currentSemester->start_date->format('Y-m');

        // 月ラベルを作成（最古の月〜現在月まで、最低1つは表示）
        $months = collect();
        $current = Carbon::createFromFormat('Y-m', $startMonth);
        $end = now();

        while ($current <= $end) {
            $months->push($current->format('Y-m'));
            $current->addMonth();
        }

        // データがなくても最低1つの月は表示
        if ($months->isEmpty()) {
            $months->push($currentSemester->start_date->format('Y-m'));
        }

        // 月別マイル獲得（現在の半期のみ）
        $monthlyMilesData = MileHistory::where('user_id', $userId)
            ->where('semester_id', $currentSemester->id)
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

        // 累積マイル計算（過去の半期の合計を初期値にする）
        $previousSemestersMiles = MileHistory::where('user_id', $userId)
            ->whereHas('semester', function($query) use ($currentSemester) {
                $query->where('start_date', '<', $currentSemester->start_date);
            })
            ->sum('miles');

        $cumulativeMiles = [];
        $total = $previousSemestersMiles;
        foreach ($monthlyMiles as $data) {
            $total += $data['total'];
            $cumulativeMiles[] = [
                'month' => $data['month'],
                'total' => $total
            ];
        }

        // カテゴリ別マイル獲得（現在の半期のみ）
        $categoryMiles = MileHistory::where('mile_histories.user_id', $userId)
            ->where('mile_histories.semester_id', $currentSemester->id)
            ->join('missions', 'mile_histories.mission_id', '=', 'missions.id')
            ->select('missions.key', DB::raw('SUM(mile_histories.miles) as total'))
            ->groupBy('missions.key')
            ->get();

        // 総統計（現在の半期のみ）
        $totalMiles = MileHistory::where('user_id', $userId)
            ->where('semester_id', $currentSemester->id)
            ->sum('miles');
        $totalActivities = MileHistory::where('user_id', $userId)
            ->where('semester_id', $currentSemester->id)
            ->count();

        return view('stats.index', compact(
            'monthlyMiles',
            'categoryMiles',
            'totalMiles',
            'totalActivities',
            'cumulativeMiles',
            'currentSemester',
            'semesters'
        ));
    }
}
