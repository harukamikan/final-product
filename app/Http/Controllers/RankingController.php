<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index()
    {
        // total_miles の降順でランキング取得
        $rankers = User::orderBy('total_miles', 'desc')->get();

        // 自分の順位を計算
        $myRank = User::where('total_miles', '>', auth()->user()->total_miles)->count() + 1;

        return view('ranking.index', compact('rankers', 'myRank'));
    }
}
