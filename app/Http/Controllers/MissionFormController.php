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
        $missionForm = MissionForm::create([
            'user_id'     => $user->id,
            'mission_id'  => $mission->id,
            'category'    => $mission->key,
            'title'       => $request->title,
            'occurred_on' => $request->occurred_on,
            'details'     => $request->details,
            'evidence_url'=> $request->evidence_url,
        ]);

        // 2) ミッション達成処理（MissionServiceを使ってマイル付与も行う）
        $missionService = app(\App\Services\MissionService::class);
        
        // Pass form data as payload for AI scoring
        $payload = [
            'title' => $request->title,
            'details' => $request->details,
            'occurred_on' => $request->occurred_on,
            'evidence_url' => $request->evidence_url,
            'mission_key' => $mission->key,
            'mission_id' => $mission->id,
        ];
        
        $achievementData = $missionService->handleTrigger(
            $user,
            $mission->trigger_type,
            $payload
        );


        // 3) タイムラインイベントを作成
        $timelineService = app(\App\Services\TimelineService::class);
        
        match($mission->key) {
            'event_host' => $timelineService->createEventHostingEvent($user, $missionForm),
            'event_speaker' => $timelineService->createEventSpeakingEvent($user, $missionForm),
            'acquire_certificate', 'certification' => $timelineService->createCertificationEvent($user, $missionForm),
            default => null,
        };

        return redirect()->route('missions.index')
            ->with('achievementData', $achievementData);
    }
}
