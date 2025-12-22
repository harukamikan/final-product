<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use Illuminate\Support\Facades\Auth;

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
            'user_id' => Auth::id(),
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

    public function index()
    {
        return view('activities.index');
    }

    public function edit($id)
    {
        $goal = Goal::findOrFail($id);
    
        return view('goals.edit', compact('goal'));
            
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'deadline' => 'required|date',
            'category' => 'nullable|string|max:255',
            'target_value' => 'nullable|max:255',
            'current_value' => 'nullable|integer',
            'criteria' => 'nullable|string',
            'memo' => 'nullable'
        ]);

         Goal::find($id)->update($validated);
        
        return redirect()->route('activities.index')
            ->with('success', '目標を更新しました！');
    }
}
