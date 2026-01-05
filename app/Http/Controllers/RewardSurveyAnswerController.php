<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RewardSurveyAnswer;
use App\Models\RewardSurvey;

class RewardSurveyAnswerController extends Controller
{
    public function store(Request $request, RewardSurvey $survey)
    {
        $request->validate([
            'answer' => 'required|string',
        ]);

        RewardSurveyAnswer::create([
            'reward_survey_id' => $survey->id,
            'user_id'          => Auth::id(),
            'company_id'       => Auth::user()->company_id,
            'answer'           => $request->answer,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'アンケートに回答しました');
    }
}
