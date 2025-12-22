<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\MissionForm;
use App\Models\UserMission;
use Illuminate\Http\Request;

class MissionFormController extends Controller
{
    public function create(Mission $mission)
    {
        return view('missions.form', compact('mission'));
    }

    public function store(Request $request, Mission $mission)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'occurred_on' => 'required|date',
            'details'     => 'nullable|string',
            'evidence_url'=> 'nullable|url',
        ]);

        // 1) フォーム提出を保存
        MissionForm::create([
            'user_id'     => auth()->id(),
            'mission_id'  => $mission->id,
            'category'    => $mission->key, // missionsテーブルにcategoryが無いのでkeyを使う
            'title'       => $request->title,
            'occurred_on' => $request->occurred_on,
            'details'     => $request->details,
            'evidence_url'=> $request->evidence_url,
        ]);

        // 2) ミッション達成処理（statusカラムが無いので completed_at のみ更新）
        //    user_missions レコードが無い場合もあるので firstOrCreate で安全にする
        $companyId = auth()->user()->company_id;

        $userMission = UserMission::firstOrCreate(
            [
                'user_id'    => auth()->id(),
                'mission_id' => $mission->id,
                'company_id' => $companyId,
            ],
            [
                'completed_at' => null,
            ]
        );

        $userMission->update([
            'completed_at' => now(),
        ]);

        // （任意）マイル付与処理をここに追加

        return redirect()->route('missions.index')
            ->with('success', 'ミッションを達成しました！');
    }
}
