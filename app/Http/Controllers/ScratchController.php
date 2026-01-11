<?php

namespace App\Http\Controllers;

use App\Services\ScratchService;
use Illuminate\Support\Facades\Auth;

class ScratchController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $points = $user->personal_mission_points;
        $canScratch = $points >= ScratchService::COST;

        return view('scratch.index', compact('points', 'canScratch'));
    }

    public function draw(ScratchService $scratch)
    {
        $user = Auth::user();

        $result = $scratch->draw($user->id);

        if (!$result) {
            return back()->with('error', 'ポイントが足りません');
        }

        return view('scratch.result', compact('result'));
    }
}
