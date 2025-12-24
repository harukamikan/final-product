<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class AdminRewardController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;

        if (!$company) {
            return view('admin.rewards.index', [
                'company' => null,
                'rewardSurveys' => collect(),
                'answeredCount' => 0,
                'unansweredCount' => 0,
            ]);
        }

        $rewardSurveys = RewardSurvey::with('user')
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        // 社員総数
        $totalUsers = User::where('company_id', $company->id)->count();

        // 回答済み人数
        $answeredCount = RewardSurvey::where('company_id', $company->id)
            ->distinct('user_id')
            ->count('user_id');

        // 未回答人数
        $unansweredCount = max(0, $totalUsers - $answeredCount);

        return view('admin.rewards.index', compact(
            'company',
            'rewardSurveys',
            'answeredCount',
            'unansweredCount'
        ));
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

    public function toggle()
    {
        $company = Auth::user()->company;

        $company->reward_survey_active = ! $company->reward_survey_active;
        $company->save();

        return redirect()
            ->route('admin.rewards.index')
            ->with('status', 'reward-survey-toggled');
    }

    public function setDeadline(Request $request)
    {
        $request->validate([
            'reward_survey_deadline' => 'required|date|after:today',
        ]);

        $company = Auth::user()->company;

        if ($company) {
            $company->reward_survey_deadline = $request->reward_survey_deadline;
            $company->save();
        }

        return redirect()
            ->route('admin.rewards.index')
            ->with('status', 'reward-survey-deadline-updated');
    }
}
