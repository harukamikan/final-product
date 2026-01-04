<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\GachaService;
use App\Models\Reward;

class RewardPlayController extends Controller
{
    /*ガチャ実行 */
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
    
    /* スクラッチ実行 */
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

    /*ガチャ・スクラッチ共通トップ画面 */
    public function gachaPage()
    {
        $user = Auth::user();

        $totalMiles = $user->total_miles ?? 0;

        //ガチャ・スクラッチが引けるか
        $canDraw = $totalMiles > 0;

        //有効な報酬が存在するか
        $hasActiveReward = Reward::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->exists();
        
        return view('gacha.index', [
            'totalMiles'     => $totalMiles,
            'canDrawGacha'   => $canDraw,
            'canDrawScratch' => $canDraw,
            'hasActiveReward'=> $hasActiveReward, 
        ]);
    }
}