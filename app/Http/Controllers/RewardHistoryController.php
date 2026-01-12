<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\RewardHistory;

class RewardHistoryController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $rawHistories = RewardHistory::with('reward')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $histories = $rawHistories->map(function ($h) {

            /*
            |--------------------------------------------------------------------------
            | 🪙 スクラッチ
            |--------------------------------------------------------------------------
            */
            if ($h->via === 'scratch') {

                // はずれ
                if ($h->result === 'lose') {
                    return [
                        'icon'       => '😢',
                        'title'      => 'スクラッチ はずれ',
                        'type'       => 'scratch',
                        'created_at' => $h->created_at,
                        'expires_at' => null,
                    ];
                }

                // マイル当たり
                if ($h->result === 'miles') {
                    return [
                        'icon'       => '🪙',
                        'title'      => "{$h->miles} マイル獲得",
                        'type'       => 'scratch',
                        'created_at' => $h->created_at,
                        'expires_at' => null,
                    ];
                }

                // 報酬当たり
                if ($h->result === 'win') {
                    return [
                        'icon'       => '🎁',
                        'title'      => $h->reward?->name ?? 'スクラッチ報酬',
                        'type'       => 'scratch',
                        'created_at' => $h->created_at,
                        'expires_at' => $h->expires_at,
                    ];
                }

                return [
                    'icon'       => '😢',
                    'title'      => 'スクラッチ（結果不明）',
                    'type'       => 'scratch',
                    'created_at' => $h->created_at,
                    'expires_at' => null,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | 🎰 ガチャ（最後のフォールバック）
            |--------------------------------------------------------------------------
            */
            return [
                'icon'       => '🎰',
                'title'      => $h->reward?->name ?? 'ガチャ報酬',
                'type'       => 'gacha',
                'created_at' => $h->created_at,
                'expires_at' => $h->expires_at,
            ];
        });

        return view('rewards.history', compact('histories'));
    }
}
