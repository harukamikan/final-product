<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\SemesterGoal;
use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class ActivityController extends Controller
{
    public function index()
    {
        //半期目標の自動切替え
        SemesterGoal::where('user_id', Auth::id())
            ->where('is_current', true)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->update(['is_current' => false]);
        
        $activities = Activity::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $pastSemesterGoals = SemesterGoal::where('user_id', Auth::id())
            ->where('is_current', false)
            ->orderByDesc('deadline')
            ->get();

        return view('activities.index', compact(
            'activities',
            'pastSemesterGoals'
        ));
    }
}
