<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RewardSurvey;

class RewardSurveyController extends Controller
{
    public function create()
    {
        return view('reward-survey.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_choice'  => 'required|string|max:255',
            'second_choice' => 'required|string|max:255',
            'third_choice'  => 'nullable|string|max:255',
        ]);

        RewardSurvey::create([
            'company_id'   => Auth::user()->company_id,
            'user_id'      => Auth::id(),
            'first_choice' => $request->first_choice,
            'second_choice'=> $request->second_choice,
            'third_choice' => $request->third_choice,
            'status'       => 'pending',
        ]);

        return redirect()->route('dashboard')
            ->with('status', 'reward-survey-submitted');
    }
}
