<?php

namespace App\Services;

use App\Models\RewardDistribution;
use App\Models\RewardHistory;
use App\Models\MileHistory;
use App\Models\User;
use App\Models\UserReward;
use Illuminate\Support\Facades\DB;

class GachaService
{
    public const COSTS = [
        'gacha'   => 100,
        'scratch' => 50,   // ← 将来変更OK
    ];

    public function cost(string $via): int
    {
        return self::COSTS[$via] ?? 0;
    }

    public function draw(int $userId, int $companyId, string $via = 'gacha')
    {
        return DB::transaction(function () use ($userId, $companyId, $via) {

            $user = User::lockForUpdate()->findOrFail($userId);

            $currentMiles = $user->mileHistories()->sum('miles');

            $cost = $this->cost($via);

            // ❗ 二重チェック（超重要）
            if ($currentMiles < $cost) {
                return null;
            }

            // マイル消費
            MileHistory::create([
                'user_id'    => $userId,
                'company_id' => $companyId,
                'miles'      => -$cost,
                'type'       => $via,
                'memo'       => "{$via} 消費",
            ]);

            // ④ 配布中の報酬を取得
            $pool = RewardDistribution::where('company_id', $companyId)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('quantity')
                        ->orWhere('quantity', '>', 0);
                })
                ->lockForUpdate()
                ->get();

            if ($pool->isEmpty()) {
                return null;
            }

            // ⑤ 抽選
            $selected = $pool->random();

            // ⑥ 数量を減らす
            if (!is_null($selected->quantity)) {
                $selected->decrement('quantity');

                if ($selected->quantity <= 0) {
                    $selected->update(['is_active' => false]);
                }
            }

            // ⑦ 報酬履歴
            RewardHistory::create([
                'user_id'   => $userId,
                'reward_id' => $selected->reward_id,
                'via'       => $via,
            ]);

            // ⑧ ユーザーが所有する報酬を作成（有効期限つき）
            UserReward::create([
                'user_id'     => $userId,
                'reward_id'   => $selected->reward_id,
                'company_id'  => $companyId,
                'acquired_at' => now(),
                'expires_at'  => $selected->reward_expires_at, // ← 重要
            ]);

            return $selected->reward;
        });
    }
}
