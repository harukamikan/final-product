<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Goal::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('activities.index', compact('activities'));
    }
}
