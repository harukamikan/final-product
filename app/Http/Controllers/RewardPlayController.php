<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\GachaService;
use App\Services\ScratchService;

class RewardPlayController extends Controller
{
    /* =======================
       ガチャ実行
    ======================= */
    public function gacha(GachaService $gacha)
    {
        $reward = $gacha->draw(
            Auth::id(),
            Auth::user()->company_id
        );

        if (!$reward) {
            return redirect()
                ->route('rewards.gacha')
                ->with('error', '現在取得できる報酬がありません');
        }

        return view('rewards.result', [
            'reward' => $reward,
            'via'    => 'gacha',
        ]);
    }

    /* =======================
       スクラッチ実行
    ======================= */
    public function scratch(\App\Services\ScratchService $scratch)
    {
        $result = $scratch->draw(Auth::id());

        if (!$result) {
            return redirect()
                ->route('rewards.scratch')
                ->with('error', 'ポイントが足りません');
        }

        return view('rewards.result', [
            'via'   => 'scratch',
            'miles' => $result['miles'],
        ]);
    }



    /* =======================
       ガチャ・スクラッチトップ画面
    ======================= */
    public function gachaPage(GachaService $gacha, ScratchService $scratch)
    {
        $user = Auth::user();

        $totalMiles = $user->total_miles ?? 0;
        $points     = $user->personal_mission_points ?? 0;

        // ガチャはマイル
        $gachaCost = GachaService::COST;
        $canDrawGacha = $totalMiles >= $gachaCost;

        // スクラッチはポイント
        $scratchCost = ScratchService::COST;
        $canDrawScratch = $points >= $scratchCost;

        return view('gacha.index', compact(
            'totalMiles',
            'points',
            'gachaCost',
            'scratchCost',
            'canDrawGacha',
            'canDrawScratch',
        ));
    }
}
