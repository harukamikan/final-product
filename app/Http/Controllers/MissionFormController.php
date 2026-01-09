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

        $user = auth()->user();

        // 1) フォーム提出を保存
        MissionForm::create([
            'user_id'     => $user->id,
            'mission_id'  => $mission->id,
            'category'    => $mission->key,
            'title'       => $request->title,
            'occurred_on' => $request->occurred_on,
            'details'     => $request->details,
            'evidence_url'=> $request->evidence_url,
        ]);

        // 2) 統合タイムラインにも保存
        $eventType = match($mission->key) {
            'event_organizer' => 'event_hosting',
            'event_speaker' => 'event_speaking',
            'acquire_certificate' => 'certification',
            default => null,
        };

        if ($eventType) {
            // ペイロードを種別ごとに構築
            $payload = match($eventType) {
                'event_hosting', 'event_speaking' => [
                    'title' => $request->title,
                    'url' => $request->evidence_url,
                    'occurred_on' => $request->occurred_on,
                    'details' => $request->details,
                    'api_fetched' => false, // フォーム入力なのでAPI未取得
                ],
                'certification' => [
                    'name' => $request->title,
                    'acquired_at' => $request->occurred_on,
                    'memo' => $request->details,
                    'organization' => null, // フォームから取得できないため
                    'score' => null,
                    'difficulty' => null,
                ],
            };

            \App\Models\TimelineEvent::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'mission_id' => $mission->id,
                'event_type' => $eventType,
                'occurred_at' => $request->occurred_on,
                'payload' => $payload,
            ]);
        }

        // 3) ミッション達成処理（MissionServiceを使ってマイル付与も行う）
        $missionService = app(\App\Services\MissionService::class);
        $achievementData = $missionService->completeManually($user, $mission);

        return redirect()->route('missions.index')
            ->with('achievementData', $achievementData);
    }
}
