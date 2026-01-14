<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterSetting extends Model
{
    protected $fillable = [
        'start_date',
        'end_date',
        'auto_reset_enabled',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_reset_enabled' => 'boolean',
    ];

    /**
     * 現在の半期設定を取得（なければデフォルト作成）
     */
    public static function current()
    {
        // 今日の日付が含まれる半期を探す
        $today = now()->toDateString();
        $current = self::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
        
        // なければ最新の半期を返す
        if (!$current) {
            $current = self::orderBy('start_date', 'desc')->first();
        }
        
        // それもなければ新規作成
        return $current ?? self::create([
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'auto_reset_enabled' => true,
        ]);
    }
}
