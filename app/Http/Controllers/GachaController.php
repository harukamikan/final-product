<?php

namespace App\Http\Controllers;

use App\Services\GachaService;
use Illuminate\Support\Facades\Auth;

class GachaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $canDrawGacha = $user->miles > 0; // 条件は自由に調整OK

        return view('gacha.index', compact('canDrawGacha'));
    }

    public function draw(GachaService $gacha)
    {
        $user = Auth::user();

        $reward = $gacha->draw(
            $user->id,
            $user->company_id,
            'gacha'
        );

        if (!$reward) {
            return back()->with('error', '現在ガチャに報酬がありません');
        }

        return view('gacha.result', compact('reward'));
    }
}
