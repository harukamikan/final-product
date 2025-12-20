<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SemesterGoal;

class SemesterGoalController extends Controller
{
    /**
     * 新規作成画面
     */
    public function create()
    {
        return view('semester-goals.create');
    }

    /**
     * 保存処理
     */
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'category'    => 'required|string|max:50',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
        ]);

        // 既存の「今期目標」を解除
        SemesterGoal::where('user_id', auth()->id())
            ->where('is_current', true)
            ->update(['is_current' => false]);

        // 新規作成
        SemesterGoal::create([
            'user_id'     => auth()->id(),
            'category'    => $validated['category'],
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'deadline'    => $validated['deadline'] ?? null,
            'is_current'  => true,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', '半期目標を設定しました');
    }

    /**
     * 編集画面
     */
    public function edit(SemesterGoal $semesterGoal)
    {
        $this->authorizeGoal($semesterGoal);

        return view('semester-goals.edit', compact('semesterGoal'));
    }

    /**
     * 更新処理
     */
    public function update(Request $request, SemesterGoal $semesterGoal)
    {
        $this->authorizeGoal($semesterGoal);

        $validated = $request->validate([
            'category'    => 'required|string|max:50',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
        ]);

        $semesterGoal->update($validated);

        return redirect()
            ->route('dashboard')
            ->with('success', '半期目標を更新しました');
    }

    /**
     * ユーザー本人チェック
     */
    private function authorizeGoal(SemesterGoal $semesterGoal)
    {
        if ($semesterGoal->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
