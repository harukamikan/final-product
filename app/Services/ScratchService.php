<?php

namespace App\Services;

use App\Models\User;
use App\Models\RewardHistory;
use App\Models\MileHistory;
use Illuminate\Support\Facades\DB;

class ScratchService
{
    /** 消費ポイント */
    public const COST = 10;

    /** 確率テーブル（案B） */
    private const TABLE = [
        ['rate' => 25, 'miles' => 0],
        ['rate' => 40, 'miles' => 5],
        ['rate' => 25, 'miles' => 10],
        ['rate' => 9,  'miles' => 20],
        ['rate' => 1,  'miles' => 50],
    ];

    /**
     * スクラッチ実行
     */
    public function draw(int $userId): ?array
    {
        return DB::transaction(function () use ($userId) {

            $user = User::lockForUpdate()->findOrFail($userId);

            // ポイント不足
            if ($user->personal_mission_points < self::COST) {
                return null;
            }

            // ポイント消費
            $user->decrement('personal_mission_points', self::COST);

            // 抽選
            $result = $this->lottery();
            $miles  = $result['miles'];

            // RewardHistory は必ず作る（はずれも含む）
            RewardHistory::create([
                'user_id' => $user->id,
                'via'     => 'scratch',
                'result'  => $miles === 0 ? 'lose' : 'miles',
                'miles'   => $miles,
            ]);

            // マイルがあれば MileHistory
            if ($miles > 0) {
                MileHistory::create([
                    'user_id'    => $user->id,
                    'company_id' => $user->company_id,
                    'miles'      => $miles,
                    'type'       => 'scratch',
                    'memo'       => 'スクラッチ獲得',
                ]);
            }

            return [
                'miles' => $miles,
            ];
        });
    }

    private function lottery(): array
    {
        $rand = random_int(1, 100);
        $sum  = 0;

        foreach (self::TABLE as $row) {
            $sum += $row['rate'];
            if ($rand <= $sum) {
                return $row;
            }
        }

        return ['rate' => 100, 'miles' => 0];
    }
}
