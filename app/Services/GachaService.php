<?php

namespace App\Services;

use App\Models\RewardDistribution;
use App\Models\RewardHistory;
use Illuminate\Support\Facades\DB;

class GachaService
{
    public function draw(int $userId, int $companyId, string $via = 'gacha')
    {
        return DB::transaction(function () use ($userId, $companyId, $via) {

            // ① 配布中の報酬を取得
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
                ->lockForUpdate() // ★同時ガチャ対策
                ->get();

            if ($pool->isEmpty()) {
                return null; // ガチャに何も入っていない
            }

            // ② ランダム抽選（今は等確率）
            $selected = $pool->random();

            // ③ 数量を減らす（制限ありの場合）
            if (!is_null($selected->quantity)) {
                $selected->decrement('quantity');

                if ($selected->quantity <= 0) {
                    $selected->update(['is_active' => false]);
                }
            }

            // ④ 履歴保存
            RewardHistory::create([
                'user_id'   => $userId,
                'reward_id' => $selected->reward_id,
                'via'       => $via, // gacha / scratch
            ]);

            return $selected->reward;
        });
    }
}
