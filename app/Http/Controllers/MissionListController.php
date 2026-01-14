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

        // PersonalMission テーブルから未完了のみ取得
        $missions = \App\Models\PersonalMission::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->orderBy('id')
            ->get();

        $totalMiles = $user->total_miles
            ?? $user->mileHistories()->sum('miles');
        $earnedThisTime = (int) session('earned_miles', 0);

        return view('missions.personal', [
            'missions'       => $missions,
            'totalMiles'     => $totalMiles,
            'earnedThisTime' => $earnedThisTime,
        ]);
    }

    public function completed(Request $request)
    {
        $user = $request->user();

        $keyword  = $request->filled('keyword') ? $request->input('keyword') : null;
        $fromDate = $request->filled('from_date') ? $request->input('from_date') : null;
        $toDate   = $request->filled('to_date') ? $request->input('to_date') : null;

        // =========================
        // 完了済みの企業ミッション（検索対応）
        // =========================
        $companyMissionsQuery = Mission::availableForUser($user->id)
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%');
            })
            ->whereHas('userMissions', function ($q) use ($user, $fromDate, $toDate) {
                $q->where('user_id', $user->id)
                    ->where(function ($qq) {
                        $qq->whereNotNull('completed_at')
                            ->orWhere('completion_count', '>=', 1);
                    })
                    ->when($fromDate, function ($qq) use ($fromDate) {
                        $qq->whereDate('completed_at', '>=', $fromDate);
                    })
                    ->when($toDate, function ($qq) use ($toDate) {
                        $qq->whereDate('completed_at', '<=', $toDate);
                    });
            })
            ->with(['userMissions' => function ($q) use ($user, $fromDate, $toDate) {
                $q->where('user_id', $user->id)
                    ->where(function ($qq) {
                        $qq->whereNotNull('completed_at')
                            ->orWhere('completion_count', '>=', 1);
                    })
                    ->when($fromDate, function ($qq) use ($fromDate) {
                        $qq->whereDate('completed_at', '>=', $fromDate);
                    })
                    ->when($toDate, function ($qq) use ($toDate) {
                        $qq->whereDate('completed_at', '<=', $toDate);
                    })
                    ->orderByDesc('completed_at');
            }])
            ->orderBy('id');

        $companyMissions = $companyMissionsQuery->get();

        // =========================
        // 完了済みの個人ミッション（検索対応）
        // =========================
        $personalMissionsQuery = \App\Models\PersonalMission::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->when($keyword, function ($q) use ($keyword) {
                // 個人ミッション側のカラム名が title じゃない場合はここを合わせてください
                $q->where('title', 'like', '%' . $keyword . '%');
            })
            ->when($fromDate, function ($q) use ($fromDate) {
                $q->whereDate('completed_at', '>=', $fromDate);
            })
            ->when($toDate, function ($q) use ($toDate) {
                $q->whereDate('completed_at', '<=', $toDate);
            })
            ->orderBy('id');

        $personalMissions = $personalMissionsQuery->get();

        // マイル系
        $totalMiles = $user->total_miles ?? $user->mileHistories()->sum('miles');

        return view('missions.completed', [
            'missions'         => $companyMissions,
            'personalMissions' => $personalMissions,
            'totalMiles'       => $totalMiles,
        ]);
    }
}
