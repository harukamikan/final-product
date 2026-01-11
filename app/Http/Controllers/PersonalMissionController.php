<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mission;
use App\Models\Tag;
use Carbon\Carbon;

class PersonalMissionController extends Controller
{
    /**
     * 個人ミッション作成フォーム表示
     */
    public function create()
    {
        $user = auth()->user();
        
        // 現在の半期目標を取得
        $currentSemesterGoal = $user->semesterGoals()
            ->where('is_current', true)
            ->first();
        
        // この半期で追加した個人ミッション数をカウント
        $personalMissionsCount = \App\Models\PersonalMission::where('user_id', $user->id)
            ->count();
        
        // 管理者設定から制限数を取得（デフォルト10）
        $maxPersonalMissions = \App\Models\AdminSetting::first()->personal_missions_limit ?? 10;
        
        $canAdd = $personalMissionsCount < $maxPersonalMissions;
        
        return view('missions.personal-create', [
            'currentSemesterGoal' => $currentSemesterGoal,
            'personalMissionsCount' => $personalMissionsCount,
            'maxPersonalMissions' => $maxPersonalMissions,
            'canAdd' => $canAdd,
        ]);
    }

    /**
     * 個人ミッション保存
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'required_count' => 'required|integer|min:1',
            'linked_category' => 'nullable|string|in:write_tech_blog,acquire_certificate,event_speaker,event_organizer',
        ]);
        
        // 制限チェック
        $personalMissionsCount = \App\Models\PersonalMission::where('user_id', $user->id)
            ->count();
        
        $maxPersonalMissions = \App\Models\AdminSetting::first()->personal_missions_limit ?? 10;
        
        if ($personalMissionsCount >= $maxPersonalMissions) {
            return back()->with('error', '個人ミッションの上限に達しました');
        }
        
        // 個人ミッション作成（PersonalMission テーブル）
        $personalMission = \App\Models\PersonalMission::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'key' => \Illuminate\Support\Str::slug($request->title),
            'linked_category' => $request->linked_category,
            'title' => $request->title,
            'description' => $request->description,
            'trigger_type' => 'manual',
            'required_count' => $request->required_count,
            'reward_miles' => 0,
            'repeatable' => false,
        ]);
        
        return redirect()->route('missions.personal')
            ->with('success', '個人ミッションを追加しました！');
    }

    /**
     * 個人ミッション編集フォーム表示
     */
    public function edit(\App\Models\PersonalMission $personalMission)
    {
        $user = auth()->user();
        
        if ($personalMission->user_id !== $user->id) {
            abort(403);
        }
        
        $createdAt = $personalMission->created_at;
        $canEdit = $createdAt->addDays(10)->isFuture();
        
        if (!$canEdit) {
            return back()->with('error', '編集期間が終了しています');
        }
        
        return view('missions.personal-edit', [
            'mission' => $personalMission,  // ← 'mission' として渡す
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * 個人ミッション更新
     */
    public function update(Request $request, \App\Models\PersonalMission $personalMission)
    {
        $user = auth()->user();
        
        if ($personalMission->user_id !== $user->id) {
            abort(403);
        }
        
        // 登録日から10日以内か確認
        $createdAt = $personalMission->created_at;
        $canEdit = $createdAt->addDays(10)->isFuture();
        
        if (!$canEdit) {
            return back()->with('error', '編集期間が終了しています');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'required_count' => 'required|integer|min:1',
            'linked_category' => 'nullable|string|in:write_tech_blog,acquire_certificate,event_speaker,event_organizer',
        ]);
        
        $personalMission->update([
            'title' => $request->title,
            'description' => $request->description,
            'required_count' => $request->required_count,
            'linked_category' => $request->linked_category,
        ]);
        
        return redirect()->route('missions.personal')
            ->with('success', 'ミッションを更新しました！');
        }

    /**
     * 個人ミッション削除
     */
    public function destroy(\App\Models\PersonalMission $personalMission)
    {
        $user = auth()->user();
        
        if ($personalMission->user_id !== $user->id) {
            abort(403);
        }
        
        // 登録日から10日以内か確認
        $createdAt = $personalMission->created_at;
        $canDelete = $createdAt->addDays(10)->isFuture();
        
        if (!$canDelete) {
            return back()->with('error', '削除期間が終了しています');
        }
        
        $personalMission->delete();
        
        return redirect()->route('missions.personal')
            ->with('success', 'ミッションを削除しました！');
    }
    /**
     * 個人ミッション完了（手動）
     */
    public function complete(\App\Models\PersonalMission $personalMission)
    {
        $user = auth()->user();
        
        if ($personalMission->user_id !== $user->id) {
            abort(403);
        }
        
        // すでに完了してたら何もしない
        if ($personalMission->completed_at) {
            return back()->with('error', 'すでに完了しています');
        }
        
        // 進捗+1
        $personalMission->increment('progress_count');
        $personalMission->refresh();
        
        // 達成したか確認
        if ($personalMission->progress_count >= $personalMission->required_count) {
            $personalMission->update(['completed_at' => now()]);
            $user->increment('personal_mission_points', 1);
            return redirect()->route('missions.personal')
                ->with('success', 'ミッション達成！🎉');
        }
        
        return redirect()->route('missions.personal')
            ->with('success', '進捗 +1！');
    }
}