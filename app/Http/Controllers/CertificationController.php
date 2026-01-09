<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimelineEvent;
use App\Services\MissionService;
use App\Models\Mission;
use Carbon\Carbon;

class CertificationController extends Controller
{
    /**
     * 資格取得フォーム表示
     */
    public function create()
    {
        // acquire_certificate ミッションを取得（存在する場合）
        $mission = Mission::where('key', 'acquire_certificate')->first();
        
        return view('certifications.create', compact('mission'));
    }
    
    /**
     * 資格取得情報を保存
     */
    public function store(Request $request, MissionService $missionService)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'acquired_at' => ['required', 'date'],
            'score' => ['nullable', 'string', 'max:100'],
            'difficulty' => ['nullable', 'string', 'max:50'],
            'memo' => ['nullable', 'string', 'max:1000'],
        ]);
        
        $user = $request->user();
        
        // timeline_events に保存
        TimelineEvent::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'event_type' => 'certification',
            'occurred_at' => Carbon::parse($request->acquired_at),
            'payload' => [
                'name' => $request->name,
                'organization' => $request->organization,
                'acquired_at' => $request->acquired_at,
                'score' => $request->score,
                'difficulty' => $request->difficulty,
                'memo' => $request->memo,
            ],
        ]);
        
        // ミッション進捗処理（ミッションが存在する場合）
        $mission = Mission::where('key', 'acquire_certificate')->first();
        if ($mission) {
            $missionService->handleTrigger(
                $user,
                'certification_acquired',
                [
                    'mission_id' => $mission->id,
                    'name' => $request->name,
                ]
            );
        }
        
        return redirect()
            ->route('timeline.index', ['type' => 'certification'])
            ->with('success', '資格取得情報を登録しました');
    }
}
