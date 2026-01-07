<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\GachaService;
use App\Models\Reward;

class RewardPlayController extends Controller
{
    /* =======================
       ガチャ実行
    ======================= */
    public function gacha(GachaService $gacha)
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

    /* =======================
       スクラッチ実行
    ======================= */
    public function scratch(GachaService $gacha)
    {
        $reward = $gacha->draw(
            Auth::id(),
            Auth::user()->company_id,
            'scratch'
        );

        if (!$reward) {
            return redirect()
                ->route('rewards.gacha')
                ->with('error', '現在取得できる報酬がありません');
        }

        return view('rewards.result', [
            'reward' => $reward,
            'via'    => 'scratch',
        ]);
    }

    /* =======================
       ガチャトップ画面
    ======================= */
    public function gachaPage(GachaService $gacha)
    {
        $user = Auth::user();

        $totalMiles = $user->total_miles ?? 0;

        $gachaCost   = $gacha->cost('gacha');
        $scratchCost = $gacha->cost('scratch');

        $canDrawGacha   = $totalMiles >= $gachaCost;
        $canDrawScratch = $totalMiles >= $scratchCost;

        // ★ Service に聞くだけ
        $hasActiveReward = $gacha->hasActiveDistribution($user->company_id);

        return view('gacha.index', compact(
            'totalMiles',
            'gachaCost',
            'scratchCost',
            'canDrawGacha',
            'canDrawScratch',
            'hasActiveReward',
        ));
    }
}
