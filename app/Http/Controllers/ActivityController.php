<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\SemesterGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index()
    {
        SemesterGoal::where('user_id', Auth::id())
            ->where('is_current', true)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->update(['is_current' => false]);
            
        //活動履歴
        $activities = Goal::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $pastSemesterGoals = SemesterGoal::where('user_id', Auth::id())
            ->where('is_current', false)
            ->orderByDesc('deadline')
            ->get();


        return view('activities.index', [
            'activities'        => $activities,
            'pastSemesterGoals' => $pastSemesterGoals,
        ]);
    }
}
