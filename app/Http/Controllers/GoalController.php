<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\Activity;
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

        $goal = Goal::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'deadline' => $request->deadline,
            'category' => $request->category,
            'target_value' => $request->target_value,
            'current_value' => $request->current_value,
            'criteria' => $request->criteria,
            'memo' => $request->memo,
        ]);

        Activity::create([
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->company_id,
            'type' => 'goal',
            'title' => '活動を記録しました：' . $goal->title,
            'date' => now(),
            'url' => route('goals.edit', $goal),
        ]);

        return redirect()->route('goals.create')->with('success', '活動を記録しました！');
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

    public function destroy($id)
    {
        $goal = Goal::findOrFail($id);

        if ($goal->user_id !== Auth::id()) {
            abort(403, 'この操作は許可されていません');
        }

        $goal->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', '活動を削除しました');
    }
}
