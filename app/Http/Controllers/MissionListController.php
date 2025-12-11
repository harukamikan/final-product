<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Http\Request;

class MissionListController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // それぞれのミッションに対して、ログインユーザーの進捗を eager load
        $missions = Mission::with(['userMissions' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }])->orderBy('id')->get();

        $totalMiles      = $user->total_miles ?? 0;
        $completedCount  = 0;
        $activeCount     = 0;

        foreach ($missions as $mission) {
            $um = $mission->userMissions->first();
            if ($um && $um->completed_at) {
                $completedCount++;
            } else {
                $activeCount++;
            }
        }

        $filter = $request->get('filter', 'all'); // all / active / completed

        return view('missions.user_index', compact(
            'missions', 'totalMiles', 'completedCount', 'activeCount', 'filter'
        ));
    }
}
