<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mission;

class MissionListController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // このユーザーが見えるミッション（自分専用 or 共有）
        $allMissions = Mission::availableForUser($user->id)
            ->with(['userMissions' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->orderBy('id')
            ->get();

        // ★ 未完了だけに絞り込む（userMission がない or completed_at が null）
        $missions = $allMissions->filter(function ($mission) {
            $userMission = $mission->userMissions->first();

            // まだ一度も触ってないミッション → 表示対象
            if (!$userMission) {
                return true;
            }

            // completed_at が null = 未完了 → 表示対象
            return is_null($userMission->completed_at);
        });

        // マイル系
        $totalMiles = $user->total_miles
            ?? $user->mileHistories()->sum('miles');

        $earnedThisTime = (int) session('earned_miles', 0);

        return view('missions.user_index', [
            'missions'       => $missions,
            'totalMiles'     => $totalMiles,
            'earnedThisTime' => $earnedThisTime,
        ]);
    }

    public function personal(Request $request)
    {
        $user = $request->user();

        // 個人ミッション（user_id が自分）のみ取得
        $allMissions = Mission::personalOnly($user->id)
            ->with(['userMissions' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->orderBy('id')
            ->get();

        // 未完了のみ
        $missions = $allMissions->filter(function ($mission) {
            $userMission = $mission->userMissions->first();

            if (!$userMission) {
                return true;
            }

            return is_null($userMission->completed_at);
        });

        $totalMiles = $user->total_miles
            ?? $user->mileHistories()->sum('miles');

        $earnedThisTime = (int) session('earned_miles', 0);

        return view('missions.user_index', [
            'missions'       => $missions,
            'totalMiles'     => $totalMiles,
            'earnedThisTime' => $earnedThisTime,
        ]);
    }

    public function completed(Request $request)
    {
        $user = $request->user();

        // 完了済みだけ（自分が見えるミッションのみ）
        $missions = Mission::availableForUser($user->id)
            ->with(['userMissions' => function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->whereNotNull('completed_at');
            }])
            ->orderBy('id')
            ->get()
            ->filter(fn ($mission) => $mission->userMissions->isNotEmpty());

        $totalMiles = $user->total_miles
            ?? $user->mileHistories()->sum('miles');

        return view('missions.completed', [
            'missions'   => $missions,
            'totalMiles' => $totalMiles,
        ]);
    }
}
