<?php

namespace App\Http\Controllers;

use App\Models\RewardSurvey;
use App\Models\RewardSurveyAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardSurveyController extends Controller
{
    // 回答フォーム表示
    public function create(RewardSurvey $rewardSurvey)
    {
        return view('reward-survey.create', compact('rewardSurvey'));
    }

    // 回答保存
    public function store(Request $request, RewardSurvey $rewardSurvey)
    {
        $request->validate([
            'first_choice'  => 'required|string|max:255',
            'second_choice' => 'required|string|max:255',
            'third_choice'  => 'nullable|string|max:255',
        ]);

        // 二重回答防止
        $exists = RewardSurveyAnswer::where('reward_survey_id', $rewardSurvey->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('error', 'すでに回答済みです');
        }

        RewardSurveyAnswer::create([
            'reward_survey_id' => $rewardSurvey->id,
            'user_id'          => Auth::id(),
            'company_id'       => Auth::user()->company_id,
            'first_choice'     => $request->first_choice,
            'second_choice'    => $request->second_choice,
            'third_choice'     => $request->third_choice,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'アンケートに回答しました');
    }
}
