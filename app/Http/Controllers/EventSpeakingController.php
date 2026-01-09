<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ConnpassService;
use App\Models\TimelineEvent;
use App\Services\MissionService;
use App\Models\Mission;

class EventSpeakingController extends Controller
{
    /**
     * イベント登壇フォーム表示
     */
    public function create()
    {
        // event_speaker ミッションを取得（存在する場合）
        $mission = Mission::where('key', 'event_speaker')->first();
        
        return view('events.speaking.create', compact('mission'));
    }
    
    /**
     * イベント登壇情報を保存
     */
    public function store(
        Request $request,
        ConnpassService $connpassService,
        MissionService $missionService
    ) {
        $request->validate([
            'event_url' => ['required', 'url'],
        ]);
        
        $user = $request->user();
        $url = $request->input('event_url');
        
        // connpass API からイベント情報を取得
        $eventData = $connpassService->fetchEventFromUrl($url);
        
        if (!$eventData) {
            // API 失敗時はURLのみ保存（フェイルセーフ）
            $eventData = [
                'url' => $url,
                'api_fetched' => false,
                'title' => 'イベント情報取得失敗',
            ];
        }
        
        // timeline_events に保存
        TimelineEvent::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'event_type' => 'event_speaking',
            'occurred_at' => isset($eventData['started_at']) 
                ? \Carbon\Carbon::parse($eventData['started_at'])
                : now(),
            'payload' => $eventData,
        ]);
        
        // ミッション進捗処理（ミッションが存在する場合）
        $mission = Mission::where('key', 'event_speaker')->first();
        if ($mission) {
            $missionService->handleTrigger(
                $user,
                'event_spoke',
                [
                    'mission_id' => $mission->id,
                    'url' => $url,
                ]
            );
        }
        
        return redirect()
            ->route('timeline.index', ['type' => 'event_speaking'])
            ->with('success', 'イベント登壇情報を登録しました');
    }
}
