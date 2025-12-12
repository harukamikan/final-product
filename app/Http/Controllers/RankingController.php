<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    public function index()
    {
        // マイルランキング
        $mileRankers = User::orderBy('total_miles', 'desc')->get();

        // ミッション数ランキング
        $missionRankers = User::orderBy('completed_missions', 'desc')->get();

        // 自分の順位
        $myMileRank = User::where(
            'total_miles',
            '>',
            Auth::user()->total_miles
        )->count() + 1;

        $myMissionRank = User::where(
            'completed_missions',
            '>',
            Auth::user()->completed_missions
        )->count() + 1;

        //ランキング対象人数
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
