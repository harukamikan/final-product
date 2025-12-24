<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRewardController extends Controller
{
    public function index()
    {
        $rewardSurveys = RewardSurvey::with('user')
            ->where('company_id', Auth::user()->company_id)
            ->latest()
            ->get();

        return view('rewards.index', compact('rewardSurveys'));
    }

    public function adopt(Request $request)
    {
        $request->validate([
            'reward_name' => 'required|string|max:255',
        ]);

        Reward::firstOrCreate(
            [
                'company_id' => Auth::user()->company_id,
                'name'       => $request->reward_name,
            ],
            [
                'description'      => 'アンケートより採用',
                'cost_miles'       => 0,
                'rank'             => 'normal',
                'expires_in_days'  => null,
                'is_active'        => false,
            ]
        );

        return back()->with('success', '報酬候補として登録しました');
    }
}

