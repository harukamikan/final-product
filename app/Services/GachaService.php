<?php
namespace App\Services;

use App\Models\RewardDistribution;
use App\Models\RewardHistory;
use App\Models\UserReward;
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
                'semester_id' => \App\Models\SemesterSetting::current()->id,
                'miles'      => -self::COST,
                'type'       => 'gacha',
                'memo'       => 'ガチャ消費',
            ]);

            // ガチャ配布プール
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

            // 所持報酬に追加
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