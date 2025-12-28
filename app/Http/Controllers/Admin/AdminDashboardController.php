<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MileHistory;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 全ユーザー数
        $totalUsers = User::count();

        // 総マイル数
        $totalMiles = MileHistory::sum('miles');

        // ランク別ユーザー数
        $users = User::all();
        $rankCounts = [
            'ブロンズ' => 0,
            'シルバー' => 0,
            'ゴールド' => 0
        ];

        foreach ($users as $user) {
            $userMiles = MileHistory::where('user_id', $user->id)->sum('miles');
            if ($userMiles >= 500) {
                $rankCounts['ゴールド']++;
            } elseif ($userMiles >= 200) {
                $rankCounts['シルバー']++;
            } else {
                $rankCounts['ブロンズ']++;
            }
        }

        // ユーザー一覧（マイル順）
        $topUsers = User::withSum('mileHistories', 'miles')
            ->orderByDesc('mile_histories_sum_miles')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalMiles',
            'rankCounts',
            'topUsers'
        ));
    }
}
