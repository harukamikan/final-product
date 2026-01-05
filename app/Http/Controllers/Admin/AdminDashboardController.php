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
        $companyId = \Illuminate\Support\Facades\Auth::user()->company_id;

        // 全ユーザー数（自社のみ）
        $totalUsers = User::where('company_id', $companyId)->count();

        // 総マイル数（自社のみ）
        $totalMiles = MileHistory::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->sum('miles');

        // ランク別ユーザー数（自社のみ）
        $users = User::where('company_id', $companyId)->get();
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

        // ユーザー一覧（マイル順、自社のみ）
        $topUsers = User::where('company_id', $companyId)
            ->withSum('mileHistories', 'miles')
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
