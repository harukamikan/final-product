<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;

class GoalController extends Controller
{
    /**
     * 編集画面を表示
     */
    public function edit($id)
    {
        // 今はダミーデータで動かす（後でDBと繋げる）
        $goal = (object)[
            'id' => $id,
            'title' => 'ブログ',
            'deadline' => '2025-03-01',
            'category' => 'ブログ',
            'target_value' => '毎日単語100個',
            'current_value' => 0,
            'achievement_criteria' => '資格取得など',
            'memo' => '',
            'priority' => '高'
        ];
        
        return view('goals.edit', compact('goal'));
    }

    /**
     * 更新処理
     */
    public function update(Request $request, $id)
    {
        // バリデーション
        $validated = $request->validate([
            'title' => 'required|max:255',
            'deadline' => 'required|date',
            'category' => 'required',
            'target_value' => 'nullable|max:255',
            'current_value' => 'nullable|integer',
            'achievement_criteria' => 'nullable|max:255',
            'memo' => 'nullable',
            'priority' => 'required|in:低,中,高'
        ]);

        // 今はダミー（後でDBに保存する処理を書く）
        // Goal::find($id)->update($validated);
        
        // 更新成功メッセージと共にリダイレクト
        return redirect()->route('goals.index')
            ->with('success', '目標を更新しました！');
    }
}
