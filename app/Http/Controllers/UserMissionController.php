<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Services\MissionService;
use Illuminate\Http\Request;

class UserMissionController extends Controller
{
    public function complete(Request $request, Mission $mission, MissionService $missionService)
    {
        $user = $request->user();

        // 手動達成用サービスを呼ぶ
        $missionService->completeManually($user, $mission);

        return back()->with('success', 'ミッションを達成として記録しました！');
    }
}
