<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterSetting extends Model
{
    protected $fillable = [
        'company_id',
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
     * 会社とのリレーション
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * 指定した会社の現在の半期設定を取得（なければデフォルト作成）
     */
    public static function current($companyId = null)
    {
        // 会社IDが指定されていなければ、ログインユーザーの会社を使う
        $companyId = $companyId ?? auth()->user()?->company_id;
        
        if (!$companyId) {
            return null;
        }

        // 今日の日付が含まれる半期を探す
        $today = now()->toDateString();
        $current = self::where('company_id', $companyId)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
        
        // なければその会社の最新の半期を返す
        if (!$current) {
            $current = self::where('company_id', $companyId)
                ->orderBy('start_date', 'desc')
                ->first();
        }
        
        // それもなければ新規作成
        return $current ?? self::create([
            'company_id' => $companyId,
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'auto_reset_enabled' => true,
        ]);
    }
}
