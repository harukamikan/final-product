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
        // 受付中でないアンケートは拒否
        abort_if(
            $rewardSurvey->status !== 'active' ||
            ($rewardSurvey->start_at && $rewardSurvey->start_at->isFuture()) ||
            ($rewardSurvey->end_at && $rewardSurvey->end_at->isPast()),
            403
        );

        // すでに回答済みなら戻す
        $alreadyAnswered = $rewardSurvey->answers()
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyAnswered) {
            return redirect()->route('dashboard')
                ->with('info', 'このアンケートは既に回答済みです。');
        }

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

        try {
            RewardSurveyAnswer::create([
                'reward_survey_id' => $rewardSurvey->id,
                'user_id'          => Auth::id(),
                'first_choice'     => $request->first_choice,
                'second_choice'    => $request->second_choice,
                'third_choice'     => $request->third_choice,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // 二重送信・リロード対策
            return redirect()->route('dashboard')
                ->with('info', 'このアンケートは既に回答済みです。');
        }

        return redirect()->route('dashboard')
            ->with('success', 'アンケートに回答しました。ありがとうございます！');
    }
}
