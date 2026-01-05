<?php

namespace App\Http\Controllers;

use App\Models\UserReward;
use Illuminate\Support\Facades\Auth;

class UserRewardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 有効な所持報酬のみ
        $userRewards = UserReward::with('reward')
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderBy('expires_at')
            ->get();

        return view('rewards.my', compact('userRewards'));
    }
}