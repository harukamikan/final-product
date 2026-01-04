<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRewardSurveyController extends Controller
{
    // 一覧
    public function index()
    {
        $companyId = Auth::user()->company_id;

        $surveys = RewardSurvey::where('company_id', $companyId)
            ->withCount('answers')
            ->latest()
            ->get();

        return view('admin.reward-surveys.index', compact('surveys'));
    }

    // 新規配布
    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'start_at'  => 'required|date',
            'end_at'    => 'nullable|date|after_or_equal:start_at',
        ]);

        RewardSurvey::create([
            'company_id' => Auth::user()->company_id,
            'title'      => $request->title,
            'start_at'   => $request->start_at,
            'end_at'     => $request->end_at,
            'status'     => 'active',
        ]);

        return redirect()
            ->route('admin.reward-surveys.index')
            ->with('success', 'アンケートを配布しました。');
    }

    // 結果表示
    public function show(RewardSurvey $rewardSurvey)
    {
        $this->authorizeCompany($rewardSurvey);

        $rewardSurvey->load(['answers.user']);

        return view('admin.reward-surveys.show', compact('rewardSurvey'));
    }

    // 配布停止
    public function stop(RewardSurvey $rewardSurvey)
    {
        $this->authorizeCompany($rewardSurvey);

        $rewardSurvey->update([
            'status' => 'stopped',
        ]);

        return back()->with('success', 'アンケートを停止しました。');
    }

    // 会社チェック（超重要）
    private function authorizeCompany(RewardSurvey $rewardSurvey)
    {
        if ($rewardSurvey->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}
