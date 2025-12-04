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

    public function index()
    {
        return view('goals.index');
    }

    public function edit($id)
    {
        $goal = (object)[
            'id' => $id,
            'title' => 'ブログ',
            'deadline' => '2025-03-01',
            'category' => 'ブログ',
            'target_value' => '毎日単語100個',
            'current_value' => 0,
            'criteria' => '資格取得など',
            'memo' => ''
        ];
        
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

        // Goal::find($id)->update($validated);
        
        return redirect()->route('goals.index')
            ->with('success', '目標を更新しました！');
    }
}
