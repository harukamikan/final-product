<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use App\Models\MileHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MissionService
{
    /**
     * 何かイベントが起きたときに呼ばれるメソッド（共通の入口）
     *
     * @param User   $user
     * @param string $triggerType  // 'tech_blog_posted' など
     * @param array  $payload      // 必要ならURLなどを渡す
     */
    public function handleTrigger(User $user, string $triggerType, array $payload = []): void
    {
        // そのトリガーに紐づくミッションを全取得
        $missions = Mission::where('trigger_type', $triggerType)->get();

        foreach ($missions as $mission) {
            $this->progressMission($user, $mission, $payload);
        }
    }

    /**
     * ミッションを1ステップ進めて、条件を満たしたらクリア＋マイル付与
     */
    protected function progressMission(User $user, Mission $mission, array $payload = []): void
    {
        // ユーザーのミッション状態レコードを取得 or 作成
        $userMission = UserMission::firstOrCreate(
            ['user_id' => $user->id, 'mission_id' => $mission->id],
            ['progress_count' => 0]
        );

        // すでにクリア済みで、repeatable じゃない場合は何もしない
        if ($userMission->isCompleted() && !$mission->repeatable) {
            return;
        }

        // 進捗を1進める（必要に応じて payload で制御してもOK）
        $userMission->progress_count += 1;

        // 達成判定
        if ($userMission->progress_count >= $mission->required_count && !$userMission->isCompleted()) {
            $userMission->completed_at = Carbon::now();

            DB::transaction(function () use ($user, $mission, $userMission) {
                $userMission->save();

                // マイル履歴作成
                MileHistory::create([
                    'user_id'    => $user->id,
                    'mission_id' => $mission->id,
                    'miles'      => $mission->reward_miles,
                    'description'     => 'mission_completed',
                ]);

                // ユーザーの合計マイルを更新（users テーブルにカラムがある想定）
                $user->increment('total_miles', $mission->reward_miles);
            });
        } else {
            // まだ達成してない場合は進捗だけ保存
            $userMission->save();
        }
    }

    public function completeManually(User $user, Mission $mission): void
{
    // trigger_type が manual系かどうかを一応チェックしても良い
    if (!str_starts_with($mission->trigger_type, 'manual')) {
        // ここで例外投げる or 何もしないでもOK
        // throw new \RuntimeException('このミッションは手動達成ではありません。');
    }

    $this->progressMission($user, $mission);
}
}
