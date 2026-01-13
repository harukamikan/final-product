<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;

class MissionCreater extends Controller
{
   public function index()
    {
        // 企業ミッション（user_id が null）のみ取得
        $query = Mission::whereNull('user_id');
        
        // 検索・フィルタ適用
        if (request('q')) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . request('q') . '%')
                  ->orWhere('key', 'like', '%' . request('q') . '%');
            });
        }
        
        if (request('trigger_type')) {
            $query->where('trigger_type', request('trigger_type'));
        }
        
        if (request('repeatable') !== null && request('repeatable') !== '') {
            $query->where('repeatable', request('repeatable'));
        }
        
        $missions = $query->orderBy('id', 'desc')->paginate(20);
        
        // 統計情報の計算（フィルタ適用後のデータに基づく）
        $allMissions = Mission::whereNull('user_id');
        
        // 検索条件を統計にも適用
        if (request('q')) {
            $allMissions->where(function ($q) {
                $q->where('title', 'like', '%' . request('q') . '%')
                  ->orWhere('key', 'like', '%' . request('q') . '%');
            });
        }
        
        if (request('trigger_type')) {
            $allMissions->where('trigger_type', request('trigger_type'));
        }
        
        if (request('repeatable') !== null && request('repeatable') !== '') {
            $allMissions->where('repeatable', request('repeatable'));
        }
        
        $repeatableCount = (clone $allMissions)->where('repeatable', true)->count();
        $avgRewardMiles = (clone $allMissions)->avg('reward_miles') ?? 0;
        $avgRewardMiles = round($avgRewardMiles);

        return view('admin.missions.index', compact('missions', 'repeatableCount', 'avgRewardMiles'));
    }

    public function create()
    {
        return view('admin.missions.create');
    }

    public function store(Request $request)
{
    $data = $this->validateData($request);

    $missionType = $data['mission_type'];
    $config      = $this->resolveMissionConfig($missionType);

    $missionData = [
        'key'            => $config['key'],
        'trigger_type'   => $config['trigger_type'],
        'title'          => $data['title'],
        'description'    => $data['description'] ?? null,
        'required_count' => $data['required_count'],
        'reward_miles'   => $data['reward_miles'],
        'repeatable'     => $data['repeatable'],
    ];

    Mission::create($missionData);

    return redirect()
        ->route('admin.missions.index')
        ->with('success', 'ミッションを作成しました。');
}

    public function edit(Mission $mission)
    {
        return view('admin.missions.edit', compact('mission'));
    }

    public function update(Request $request, Mission $mission)
{
    $data = $this->validateData($request, $mission->id);

    $missionType = $data['mission_type'];
    $config      = $this->resolveMissionConfig($missionType);

    $missionData = [
        'key'            => $config['key'],
        'trigger_type'   => $config['trigger_type'],
        'title'          => $data['title'],
        'description'    => $data['description'] ?? null,
        'required_count' => $data['required_count'],
        'reward_miles'   => $data['reward_miles'],
        'repeatable'     => $data['repeatable'],
    ];

    $mission->update($missionData);

    return redirect()
        ->route('admin.missions.index')
        ->with('success', 'ミッションを更新しました。');
}


    public function destroy(Mission $mission)
    {
        $mission->delete();

        return redirect()
            ->route('admin.missions.index')
            ->with('success', 'ミッションを削除しました。');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        // まずmission_typeを取得してkeyに変換
        $missionType = $request->input('mission_type');
        
        if (!$missionType) {
            // mission_typeがない場合は先に基本バリデーションを実行
            return $request->validate([
                'mission_type' => ['required', 'in:write_tech_blog,event_speaker,event_organizer,acquire_certificate'],
            ]);
        }
        
        $config = $this->resolveMissionConfig($missionType);
        $missionKey = $config['key'];
        
        return $request->validate([
            'mission_type'   => ['required', 'in:write_tech_blog,event_speaker,event_organizer,acquire_certificate'],
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'required_count' => ['required', 'integer', 'min:1'],
            'reward_miles'   => ['required', 'integer', 'min:0', new \App\Rules\MissionMilesRule($missionKey)],
            'repeatable'     => ['required', 'boolean'],
        ]);
    }

    private function resolveMissionConfig(string $missionType): array
{
    // mission_type ごとの key と trigger_type をここで定義
    return match ($missionType) {
        'write_tech_blog' => [
            'key'          => 'write_tech_blog',
            'trigger_type' => 'tech_blog_posted',
        ],
        'event_speaker' => [
            'key'          => 'event_speaker',
            'trigger_type' => 'google_form_submitted',
        ],
        'event_organizer' => [
            'key'          => 'event_organizer',
            'trigger_type' => 'google_form_submitted',
        ],
        'acquire_certificate' => [
            'key'          => 'acquire_certificate',
            'trigger_type' => 'google_form_submitted',
        ],
        default => throw new \InvalidArgumentException('Unknown mission type: ' . $missionType),
    };
}

}
