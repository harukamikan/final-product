<?php

namespace App\Services;

class MissionLimitsService
{
    /**
     * 指定されたミッションkeyに対する範囲情報を取得
     *
     * @param string $missionKey
     * @return array{min_miles: int, max_miles: int}
     * @throws \InvalidArgumentException keyが未定義の場合
     */
    public function getLimits(string $missionKey): array
    {
        $limits = config("mission_limits.{$missionKey}");
        
        if (!$limits) {
            throw new \InvalidArgumentException("Mission key '{$missionKey}' is not defined in mission_limits config.");
        }
        
        return $limits;
    }
    
    /**
     * 最小マイルを取得
     *
     * @param string $missionKey
     * @return int
     */
    public function getMinMiles(string $missionKey): int
    {
        return $this->getLimits($missionKey)['min_miles'];
    }
    
    /**
     * 最大マイルを取得
     *
     * @param string $missionKey
     * @return int
     */
    public function getMaxMiles(string $missionKey): int
    {
        return $this->getLimits($missionKey)['max_miles'];
    }
    
    /**
     * 指定されたマイルが範囲内かチェック
     *
     * @param string $missionKey
     * @param int $miles
     * @return bool
     */
    public function isWithinRange(string $missionKey, int $miles): bool
    {
        $limits = $this->getLimits($missionKey);
        return $miles >= $limits['min_miles'] && $miles <= $limits['max_miles'];
    }
}
