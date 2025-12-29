<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\RewardHistory;
use Illuminate\Support\Facades\Auth;

class RewardPlayController extends Controller
{
    public function index()
    {
        return view('rewards.play');
    }

    public function gacha()
    {
        $reward = Reward::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        RewardHistory::create([
            'user_id' => Auth::id(),
            'reward_id' => $reward->id,
            'source' => 'gacha',
        ]);

        return back()->with('result', $reward);
    }

    public function scratch()
    {
        $reward = Reward::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        RewardHistory::create([
            'user_id' => Auth::id(),
            'reward_id' => $reward->id,
            'source' => 'scratch',
        ]);

        return back()->with('result', $reward);
    }
}

