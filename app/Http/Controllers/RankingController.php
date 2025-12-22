<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    public function index()
    {
        $me = Auth::user();
        $companyId = $me->company_id;

        // middleware で弾いてる想定だけど保険
        abort_if(!$companyId, 403, '会社に所属していません');

        // マイルランキング（社内のみ）
        $mileRankers = User::where('company_id', $companyId)
            ->orderByDesc('total_miles')
            ->get();

        // ミッション数ランキング（社内のみ）
        $missionRankers = User::where('company_id', $companyId)
            ->orderByDesc('completed_missions')
            ->get();

        // 自分の順位（社内のみ）
        $myMileRank = User::where('company_id', $companyId)
            ->where('total_miles', '>', $me->total_miles)
            ->count() + 1;

        $myMissionRank = User::where('company_id', $companyId)
            ->where('completed_missions', '>', $me->completed_missions)
            ->count() + 1;

        // ランキング対象人数（社内のみ）
        $mileUserCount = $mileRankers->count();
        $missionUserCount = $missionRankers->count();

        return view('ranking.index', compact(
            'mileRankers',
            'missionRankers',
            'myMileRank',
            'myMissionRank',
            'mileUserCount',
            'missionUserCount'
        ));
    }
}
