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
                'rewardStats' => [],
                'answeredCount' => 0,
                'unansweredCount' => 0,
            ]);
        }

        // 社員別（既存）
        $rewardSurveys = RewardSurvey::with('user')
            ->where('company_id', $company->id)
            ->get();

        // ===== ② 報酬別集計 =====
        $rewardStats = [];

        foreach ($rewardSurveys as $survey) {
            foreach (
                [
                    $survey->first_choice,
                    $survey->second_choice,
                    $survey->third_choice,
                ] as $choice
            ) {

                if (!$choice) continue;

                if (!isset($rewardStats[$choice])) {
                    $rewardStats[$choice] = [
                        'count' => 0,
                        'users' => [],
                    ];
                }

                $rewardStats[$choice]['count']++;
                $rewardStats[$choice]['users'][] = $survey->user;
            }
        }

        // 希望人数が多い順に並び替え
        uasort($rewardStats, fn($a, $b) => $b['count'] <=> $a['count']);

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
            'rewardStats',
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

    public function decide(Request $request)
    {
        $request->validate([
            'survey_id' => 'required|exists:reward_surveys,id',
            'reward_id' => 'required',
        ]);

        $survey = RewardSurvey::findOrFail($request->survey_id);

        Reward::create([
            'company_id' => Auth::user()->company_id,
            'user_id'    => $survey->user_id,
            'name'       => $request->reward_id,
            'status'     => 'decided',
        ]);

        return back()->with('success', '報酬を決定しました');
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

    public function bulkDecide(Request $request)
    {
        $request->validate([
            'reward_name' => 'required|string',
        ]);

        $companyId = Auth::user()->company_id;

        Reward::firstOrCreate(
            [
                'company_id' => $companyId,
                'name'       => $request->reward_name,
            ],
            [
                'description' => 'アンケートより採用',
                'cost_miles'  => 0,
                'rank'        => 'normal',
                'is_active'   => false,
            ]
        );

        return back()->with('success', 'この報酬を採用しました');
    }
}
