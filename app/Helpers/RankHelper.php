<?php

namespace App\Helpers;

class RankHelper
{
    /**
     * マイル数からランク名を計算
     */
    public static function calculateRank(int $miles): string
    {
        if ($miles >= 500) return 'ゴールド';
        if ($miles >= 200) return 'シルバー';
        return 'ブロンズ';
    }

    /**
     * ランク情報を取得（ランクアップ検出含む）
     */
    public static function getRankInfo(int $currentMiles, int $previousMiles): array
    {
        $currentRank = self::calculateRank($currentMiles);
        $previousRank = self::calculateRank($previousMiles);
        $rankUp = $currentRank !== $previousRank;

        $nextRankInfo = self::getNextRankInfo($currentRank, $currentMiles);

        return [
            'current_rank' => $currentRank,
            'previous_rank' => $previousRank,
            'rank_up' => $rankUp,
            'next_rank' => $nextRankInfo['next_rank'],
            'miles_to_next' => $nextRankInfo['miles_to_next'],
            'current_miles' => $currentMiles,
        ];
    }

    /**
     * 次のランク情報を取得
     */
    public static function getNextRankInfo(string $currentRank, int $currentMiles): array
    {
        $ranks = [
            'ブロンズ' => ['threshold' => 0, 'next' => 'シルバー', 'next_threshold' => 200],
            'シルバー' => ['threshold' => 200, 'next' => 'ゴールド', 'next_threshold' => 500],
            'ゴールド' => ['threshold' => 500, 'next' => null, 'next_threshold' => null],
        ];

        $rankData = $ranks[$currentRank] ?? $ranks['ブロンズ'];

        return [
            'next_rank' => $rankData['next'],
            'miles_to_next' => $rankData['next_threshold'] ? $rankData['next_threshold'] - $currentMiles : null,
        ];
    }

    /**
     * ランクの色を取得（CSS用）
     */
    public static function getRankColor(string $rank): string
    {
        return match($rank) {
            'ゴールド' => 'from-yellow-400 to-yellow-600',
            'シルバー' => 'from-gray-300 to-gray-500',
            'ブロンズ' => 'from-orange-400 to-orange-600',
            default => 'from-gray-400 to-gray-600',
        };
    }

    /**
     * ランクのテキスト色を取得（CSS用）
     */
    public static function getRankTextColor(string $rank): string
    {
        return match($rank) {
            'ゴールド' => 'text-yellow-600',
            'シルバー' => 'text-gray-600',
            'ブロンズ' => 'text-orange-600',
            default => 'text-gray-600',
        };
    }

    /**
     * 進捗率を計算（0-100）
     */
    public static function calculateProgress(int $currentMiles, string $currentRank): int
    {
        $ranks = [
            'ブロンズ' => ['min' => 0, 'max' => 200],
            'シルバー' => ['min' => 200, 'max' => 500],
            'ゴールド' => ['min' => 500, 'max' => 500],
        ];

        $rankData = $ranks[$currentRank] ?? $ranks['ブロンズ'];

        if ($currentRank === 'ゴールド') {
            return 100;
        }

        $range = $rankData['max'] - $rankData['min'];
        $progress = $currentMiles - $rankData['min'];

        return min(100, (int) (($progress / $range) * 100));
    }
}
