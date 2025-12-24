<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardSurvey;
use Illuminate\Support\Facades\Auth;

class AdminRewardController extends Controller
{
    public function index()
    {
        $rewardSurveys = RewardSurvey::with('user')
            ->where('company_id', Auth::user()->company_id)
            ->latest()
            ->get();

        return view('admin.rewards.index', compact('rewardSurveys'));
    }
}
