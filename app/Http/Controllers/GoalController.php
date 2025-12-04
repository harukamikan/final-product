<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;

class GoalController extends Controller
{
    public function create()
    {
        return view('goals.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'deadline' => 'required|date',
            'category' => 'nullable|string|max:255',
            'target_value' => 'nullable|string|max:255',
            'current_value' => 'nullable|integer',
            'criteria' => 'nullable|string',
            'memo' => 'nullable|string',
        ]);

        Goal::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'deadline' => $request->deadline,
            'category' => $request->category,
            'target_value' => $request->target_value,
            'current_value' => $request->current_value,
            'criteria' => $request->criteria,
            'memo' => $request->memo,
        ]);

        return redirect()->route('goals.create')->with('success', '目標を追加しました！');
    }
}
