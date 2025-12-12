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
     * @param array  $payload      // ['mission_key' => ..., 'url' => ...] など
     *
     * @return int 今回新たに獲得したマイル数の合計
     */
    public function handleTrigger(User $user, string $triggerType, array $payload = []): int
    {
        $query = Mission::where('trigger_type', $triggerType);

        // mission_key が指定されていたら、特定のミッションだけ対象にする
        if (!empty($payload['mission_key'])) {
            $query->where('key', $payload['mission_key']);
        }

        $missions = $query->get();

        $earnedTotal = 0;

        foreach ($missions as $mission) {
            $earnedTotal += $this->progressMission($user, $mission, $payload);
        }

        return $earnedTotal;
    }

    /**
     * ミッションを1ステップ進めて、条件を満たしたらクリア＋マイル付与
     *
     * @return int このミッションで今回新たに獲得したマイル数
     */
    protected function progressMission(User $user, Mission $mission, array $payload = []): int
    {
        // ユーザーのミッション状態レコードを取得 or 作成
        $userMission = UserMission::firstOrCreate(
            ['user_id' => $user->id, 'mission_id' => $mission->id],
            ['progress_count' => 0]
        );

        // ★ URL が来ていたら proof_url に保存（最新のものに更新）
        if (!empty($payload['url'])) {
            $userMission->proof_url = $payload['url'];
        }

        // すでにクリア済みで、repeatable じゃない場合は何もしない
        if ($userMission->isCompleted() && !$mission->repeatable) {
            // ただし proof_url だけは更新される可能性があるので save しておく
            $userMission->save();
            return 0;
        }

        // 進捗を1進める
        $userMission->progress_count += 1;

        // まだ達成していない or すでに completed_at が入っている場合 → 進捗だけ保存
        if ($userMission->progress_count < $mission->required_count || $userMission->isCompleted()) {
            $userMission->save();
            return 0;
        }

        // ここに来たら「今ちょうど達成した」
        $earned = (int) $mission->reward_miles;

        DB::transaction(function () use ($user, $mission, $userMission, $earned, $payload) {
            // 念のためトランザクション内でも URL を反映
            if (!empty($payload['url'])) {
                $userMission->proof_url = $payload['url'];
            }

            $userMission->completed_at = Carbon::now();
            $userMission->save();

            // マイル履歴作成
            MileHistory::create([
                'user_id'     => $user->id,
                'mission_id'  => $mission->id,
                'miles'       => $earned,
                'type'        => 'earn',
                'description' => 'mission_completed',
            ]);

            // ユーザーの合計マイルを更新
            $user->increment('total_miles', $earned);

            //達成ミッション数を加算
            $user->increment('completed_missions');
        });

        return $earned;
    }

    public function completeManually(User $user, Mission $mission): int
    {
        // trigger_type が manual系かどうかを一応チェックしても良い
        if (!str_starts_with($mission->trigger_type, 'manual')) {
            // 必要ならここで例外など
            // throw new \RuntimeException('このミッションは手動達成ではありません。');
        }

        return $this->progressMission($user, $mission);
    }
}
