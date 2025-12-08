<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\UserMission;
use App\Models\MileHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MissionService
{
    public function completeWriteTechBlogMission(User $user, string $url): void
    {
        $mission = Mission::where('code', 'WRITE_TECH_BLOG')->firstOrFail();

        $userMission = UserMission::firstOrCreate(
            [
                'user_id'   => $user->id,
                'mission_id'=> $mission->id,
            ],
            [
                'status' => 'pending',
            ]
        );

        // すでにクリア済みなら何もしない
        if ($userMission->status === 'completed') {
            return;
        }

        DB::transaction(function () use ($user, $mission, $userMission, $url) {
            // ミッション達成
            $userMission->status = 'completed';
            $userMission->proof_url = $url;
            $userMission->completed_at = now();
            $userMission->save();

            // マイル履歴を追加
            MileHistory::create([
                'user_id'   => $user->id,
                'mission_id'=> $mission->id,
                'miles'     => $mission->reward_miles,
                'type'      => 'earn',
                'description' => '技術ブログ投稿ミッション達成',
            ]);

            // ユーザーの総マイルを更新
            $user->increment('total_miles', $mission->reward_miles);
        });
    }
}
