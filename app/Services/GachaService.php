<?php

namespace App\Services;

use App\Models\RewardDistribution;
use App\Models\RewardHistory;
use App\Models\MileHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GachaService
{
    public const COST = 100;

    public function draw(int $userId, int $companyId)
    {
        return DB::transaction(function () use ($userId, $companyId) {

            $user = User::lockForUpdate()->findOrFail($userId);

            // 現在のマイル確認
            $currentMiles = $user->mileHistories()->sum('miles');
            if ($currentMiles < self::COST) {
                return null;
            }

            // マイル消費
            MileHistory::create([
                'user_id'    => $userId,
                'company_id' => $companyId,
                'miles'      => -self::COST,
                'type'       => 'gacha',
                'memo'       => 'ガチャ消費',
            ]);

            // ガチャ配布プール
            $pool = RewardDistribution::where('company_id', $companyId)
                ->available()
                ->lockForUpdate()
                ->get();

            if ($pool->isEmpty()) {
                return null;
            }

            $selected = $pool->random();

            // 数量管理
            if (!is_null($selected->quantity)) {
                $selected->decrement('quantity');
                if ($selected->quantity <= 0) {
                    $selected->update(['is_active' => false]);
                }
            }

            // 履歴保存（ガチャ）
            RewardHistory::create([
                'user_id'    => $userId,
                'reward_id'  => $selected->reward_id,
                'via'        => 'gacha',
                'expires_at' => $selected->reward_expires_at,
            ]);

            return $selected->reward;
        });
    }
}
