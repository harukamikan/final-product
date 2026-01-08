<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardDistributionController extends Controller
{
    // 一覧
    public function index()
    {
        $companyId = Auth::user()->company_id;

        $distributions = RewardDistribution::with('reward')
            ->where('company_id', $companyId)
            ->latest()
            ->get();

        $rewards = Reward::where('company_id', $companyId)->get();

        return view('admin.reward_distributions.index', compact(
            'distributions',
            'rewards'
        ));
    }

    // 新規作成
    public function store(Request $request)
    {
        $request->validate([
            'reward_id'         => 'required|exists:rewards,id',
            'quantity'          => 'nullable|integer|min:1',
            'starts_at'         => 'nullable|date',
            'ends_at'           => 'nullable|date|after_or_equal:starts_at',
            'reward_expires_at' => 'nullable|date',
        ]);

        RewardDistribution::create([
            'company_id'         => Auth::user()->company_id,
            'reward_id'          => $request->reward_id,
            'quantity'           => $request->quantity,
            'starts_at'          => $request->starts_at,
            'ends_at'            => $request->ends_at,
            'reward_expires_at'  => $request->reward_expires_at,
            'is_active'          => true,
        ]);

        return back()->with('success', '報酬を決定し、配布を開始しました');
    }


    // ON / OFF 切り替え
    public function toggle(RewardDistribution $distribution)
    {
        if ($distribution->is_expired) {
            return back()->with('error', '期限切れの報酬は変更できません');
        }

        $distribution->update([
            'is_active' => ! $distribution->is_active,
        ]);

        return back();
    }

    
    //　削除
    public function destroy(RewardDistribution $distribution)
    {
        if ($distribution->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $distribution->delete();

        return back()->with('success', '配布候補を削除しました');
    }
}
