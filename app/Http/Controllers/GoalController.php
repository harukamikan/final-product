<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class GoalController extends Controller
{
    /*
     * 新規作成画面
     */
    public function create()
    {
        return view('goals.create');
    }

    /*
     *　目標作成 + Actibity 登録
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'deadline' => 'required|date',
            'category' => 'nullable|string|max:255',
            'target_value' => 'nullable|string|max:255',
            'current_value' => 'nullable|integer',
            'criteria' => 'nullable|string',
            'memo' => 'nullable|string',
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

        return redirect()->route('dashboard')->with('success', '活動を記録しました！');
    }

    /**
     * 編集画面
     */
    public function edit($id)
    {
        $goal = Goal::findOrFail($id);

        //権限チェック
        if ($goal->user_id !== Auth::id()) {
            abort(403, 'この操作は許可されていません');
        }

        $activityDate = Activity::where('type', 'goal')
            ->where('user_id', Auth::id())
            ->where('url', route('goals.edit', $goal))
            ->value('date');

        return view('goals.edit', compact('goal', 'activityDate'));
    }

    /**
     * 更新 + Activity 同期
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'memo' => 'nullable|string',
            'activity_date' => 'required|date',
        ]);

        $goal = Goal::findOrFail($id);

        //権限チェック
        if ($goal->user_id !== Auth::id()) {
            abort(403, 'この操作は許可されていません');
        }

        //Goal更新
        $goal->update([
            'title' => $validated['title'],
            'memo'  => $validated['memo'] ?? null,
        ]);

        //Activityの活動日を更新
        Activity::where('type', 'goal')
            ->where('user_id', Auth::id())
            ->where('url', route('goals.edit', $goal))
            ->update([
                'title' => '活動を記録しました：' . $goal->title,
                'date' => $validated['activity_date'],
            ]);

        return redirect()->route('activities.index')
            ->with('success', '活動を更新しました！');
    }

    /**
     * 削除
     */
    public function destroy($id)
    {
        $goal = Goal::findOrFail($id);

        //権限チェック
        if ($goal->user_id !== Auth::id()) {
            abort(403, 'この操作は許可されていません');
        }

        //Goal削除
        $goal->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', '活動を削除しました');
    }
}
