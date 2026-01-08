<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RewardDistribution;

class DeactivateExpiredRewardDistributions extends Command
{
    /**
     * コマンド名
     */
    protected $signature = 'rewards:deactivate-expired';

    /**
     * 説明
     */
    protected $description = '期限切れの報酬配布を自動で停止する';

    public function handle(): int
    {
        $now = now();

        $count = RewardDistribution::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query
                    // 配布終了日時が過ぎている
                    ->where(function ($q) use ($now) {
                        $q->whereNotNull('ends_at')
                            ->where('ends_at', '<', $now);
                    })
                    // または 報酬の有効期限が過ぎている
                    ->orWhere(function ($q) use ($now) {
                        $q->whereNotNull('reward_expires_at')
                            ->where('reward_expires_at', '<', $now);
                    });
            })
            ->update([
                'is_active' => false,
            ]);

        $this->info("{$count} 件の期限切れ報酬を停止しました");

        return Command::SUCCESS;
    }
}
