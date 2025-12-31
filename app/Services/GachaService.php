<?php

namespace App\Services;

use App\Models\RewardDistribution;
use App\Models\RewardHistory;
use App\Models\MileHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GachaService
{
    const COST_MILES = 100; // 🎰 ガチャ1回の必要マイル

    public function draw(int $userId, int $companyId, string $via = 'gacha')
    {
        return DB::transaction(function () use ($userId, $companyId, $via) {

            // ① ユーザー取得
            $user = User::lockForUpdate()->findOrFail($userId);

            // ② 現在のマイル残高を計算
            $currentMiles = $user->mileHistories()->sum('miles');

            if ($currentMiles < self::COST_MILES) {
                return null; // マイル不足
            }

            // ③ マイル消費
            MileHistory::create([
                'user_id'    => $userId,
                'company_id' => $companyId,
                'miles'      => -self::COST_MILES,
                'type'       => $via,
                'memo'       => 'ガチャ消費',
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

            return $selected->reward;
        });
    }
}
