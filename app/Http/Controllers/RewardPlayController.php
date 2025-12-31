<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\GachaService;
use App\Models\Reward;

class RewardPlayController extends Controller
{
    public function gacha(\App\Services\GachaService $gacha)
    {
        $reward = $gacha->draw(
            Auth::id(),
            Auth::user()->company_id,
            'gacha'
        );

        return view('rewards.result', [
            'reward' => $reward,
            'via'    => 'gacha',
        ]);
    }

    public function scratch(GachaService $gacha)
    {
        $reward = $gacha->draw(
            Auth::id(),
            Auth::user()->company_id,
            'scratch'
        );

        return view('rewards.result', [
            'reward' => $reward,
            'via'    => 'scratch',
        ]);
    }

    public function gachaPage()
    {
        $user = Auth::user();

        $totalMiles = $user->total_miles ?? 0;

        //ガチャが引けるか
        $canDrawGacha = $user->total_miles > 0;

        //有効なガチャ報酬があるか
        $hasActiveGachaReward = Reward::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->exists();
        
        return view('gacha.index', compact(
            'totalMiles',
            'canDrawGacha',
            'hasActiveGachaReward'
        ));
    }
}
