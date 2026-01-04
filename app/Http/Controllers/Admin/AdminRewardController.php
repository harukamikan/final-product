<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardSurvey;
use App\Models\RewardSurveyAnswer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRewardController extends Controller
{
    /**
     * 報酬決定（アンケート結果表示）
     */
    public function index()
    {
        $company = Auth::user()->company;

        if (!$company) {
            return view('admin.rewards.index', [
                'company'         => null,
                'activeSurvey'    => null,
                'rewardStats'     => [],
                'answeredCount'   => 0,
                'unansweredCount' => 0,
            ]);
        }

        $companyId = $company->id;

        // ===== 現在アクティブなアンケート（配布単位）=====
        $activeSurvey = RewardSurvey::where('company_id', $companyId)
            ->where('status', 'active')
            ->latest()
            ->first();

        // 社員総数
        $totalUsers = User::where('company_id', $companyId)->count();

        // 回答数
        $answeredCount = $activeSurvey
            ? RewardSurveyAnswer::where('reward_survey_id', $activeSurvey->id)
                ->distinct('user_id')
                ->count('user_id')
            : 0;

        // 未回答数
        $unansweredCount = max(0, $totalUsers - $answeredCount);

        // ===== 人気順集計 =====
        $rewardStats = [];

        if ($activeSurvey) {
            $answers = RewardSurveyAnswer::where('reward_survey_id', $activeSurvey->id)->get();

            foreach ($answers as $answer) {
                foreach ([
                    $answer->first_choice,
                    $answer->second_choice,
                    $answer->third_choice,
                ] as $choice) {

                    if (!$choice) continue;

                    if (!isset($rewardStats[$choice])) {
                        $rewardStats[$choice] = ['count' => 0];
                    }

                    $rewardStats[$choice]['count']++;
                }
            }

            // 希望数の多い順に並び替え
            uasort($rewardStats, fn ($a, $b) => $b['count'] <=> $a['count']);
        }

        return view('admin.rewards.index', compact(
            'company',
            'activeSurvey',
            'rewardStats',
            'answeredCount',
            'unansweredCount'
        ));
    }

    /**
     * アンケート開始 / 停止
     */
    public function toggle()
    {
        $company = Auth::user()->company;

        if (!$company) {
            return back();
        }

        // 現在アクティブなアンケート
        $activeSurvey = RewardSurvey::where('company_id', $company->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($activeSurvey) {
            // ===== 停止 =====
            $activeSurvey->update([
                'status' => 'stopped',
            ]);

            return redirect()
                ->route('admin.rewards.index')
                ->with('success', 'アンケートを停止しました。');
        }

        // ===== 開始（新規配布）=====
        RewardSurvey::create([
            'company_id' => $company->id,
            'title'      => '報酬アンケート',
            'start_at'   => now(),
            'end_at'     => null,
            'status'     => 'active',
        ]);

        return redirect()
            ->route('admin.rewards.index')
            ->with('success', 'アンケートを開始しました。');
    }

    /**
     * 回答期限の設定
     */
    public function setDeadline(Request $request)
    {
        $request->validate([
            'end_at' => 'required|date|after:today',
        ]);

        $company = Auth::user()->company;

        if (!$company) {
            return back();
        }

        $activeSurvey = RewardSurvey::where('company_id', $company->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$activeSurvey) {
            return back()->with('error', '実施中のアンケートがありません。');
        }

        $activeSurvey->update([
            'end_at' => $request->end_at,
        ]);

        return redirect()
            ->route('admin.rewards.index')
            ->with('success', '回答期限を設定しました。');
    }

    /**
     * 報酬候補として登録（アンケート結果から）
     */
    public function bulkDecide(Request $request)
    {
        $request->validate([
            'reward_name' => 'required|string',
        ]);

        $companyId = Auth::user()->company_id;

        Reward::firstOrCreate(
            [
                'company_id' => $companyId,
                'name'       => $request->reward_name,
            ],
            [
                'description' => 'アンケートより採用',
                'cost_miles'  => 0,
                'rank'        => 'normal',
                'is_active'   => false,
            ]
        );

        return back()->with('success', 'この報酬を採用しました。');
    }
}
