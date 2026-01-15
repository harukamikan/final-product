<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardUsageController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;

        // 使用申請された報酬（used_atがある）を取得
        $usedRewards = UserReward::with(['user', 'reward'])
            ->where('company_id', $companyId)
            ->whereNotNull('used_at')
            ->orderByRaw('resolved_at IS NOT NULL')  // 未解決を上に
            ->orderBy('used_at', 'desc')
            ->get();

        return view('admin.rewards.usage', compact('usedRewards'));
    }

    public function resolve($id)
    {
        $userReward = UserReward::findOrFail($id);

        // 自社の報酬かチェック
        if ($userReward->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $userReward->resolved_at = now();
        $userReward->save();

        return back()->with('success', '解決済みにしました');
    }

    public function unresolve($id)
    {
        $userReward = UserReward::findOrFail($id);

        if ($userReward->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $userReward->resolved_at = null;
        $userReward->save();

        return back()->with('success', '未解決に戻しました');
    }
}