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
        'scratch' => 50,
    ];

    /* =======================
       消費マイル取得
    ======================= */
    public function cost(string $via): int
    {
        return self::COSTS[$via] ?? 0;
    }

    /* =======================
       配布中の報酬があるか
    ======================= */
    public function hasActiveDistribution(int $companyId): bool
    {
        return RewardDistribution::where('company_id', $companyId)
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
            ->exists();
    }

    /* =======================
       ガチャ / スクラッチ実行
    ======================= */
    public function draw(int $userId, int $companyId, string $via = 'gacha')
    {
        return DB::transaction(function () use ($userId, $companyId, $via) {

            // ユーザー取得（排他ロック）
            $user = User::lockForUpdate()->findOrFail($userId);

            // 現在のマイル
            $currentMiles = $user->mileHistories()->sum('miles');

            $cost = $this->cost($via);

            // マイル不足チェック（二重防御）
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

            // 配布中報酬プール
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

            // 抽選
            $selected = $pool->random();

            // 数量管理
            if (!is_null($selected->quantity)) {
                $selected->decrement('quantity');

                if ($selected->quantity <= 0) {
                    $selected->update(['is_active' => false]);
                }
            }

            // 報酬履歴
            RewardHistory::create([
                'user_id'   => $userId,
                'reward_id' => $selected->reward_id,
                'via'       => $via,
                'expires_at' => $selected->reward_expires_at,
            ]);

            // ユーザー報酬（有効期限付き）
            UserReward::create([
                'user_id'     => $userId,
                'reward_id'   => $selected->reward_id,
                'company_id'  => $companyId,
                'acquired_at' => now(),
                'expires_at'  => $selected->reward_expires_at,
            ]);

            return $selected->reward;
        });
    }
}
