<?php

namespace App\Http\Controllers;

use App\Services\GachaService;
use Illuminate\Support\Facades\Auth;

class GachaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalMiles = $user->total_miles;
        $canDrawGacha = $totalMiles >= GachaService::COST;

        return view('gacha.index', compact('canDrawGacha', 'totalMiles'));
    }

    public function draw(GachaService $gacha)
    {
        $user = Auth::user();

        $reward = $gacha->draw(
            $user->id,
            $user->company_id
        );

        if (!$reward) {
            return back()->with('error', '現在ガチャに報酬がありません');
        }

        return view('gacha.result', compact('reward'));
    }
}
