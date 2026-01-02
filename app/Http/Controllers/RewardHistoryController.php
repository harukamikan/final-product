<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\RewardHistory;


class RewardHistoryController extends Controller
{
    public function index()
    {
        $histories = RewardHistory::with('reward')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('rewards.history', compact('histories'));
    }
}

