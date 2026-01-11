<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\RewardHistory;
use App\Models\MileHistory;
use Illuminate\Support\Collection;

class RewardHistoryController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // ガチャ履歴
        $gachaHistories = RewardHistory::with('reward')
            ->where('user_id', $userId)
            ->where('via', 'gacha')
            ->latest()
            ->get()
            ->map(function ($h) {
                return [
                    'type'       => 'gacha',
                    'title'      => $h->reward->name ?? '(不明な報酬)',
                    'created_at' => $h->created_at,
                    'expires_at' => $h->expires_at,
                    'icon'       => '🎰',
                ];
            });


        // 🪙 スクラッチ履歴
        $scratchHistories = MileHistory::where('user_id', $userId)
            ->where('type', 'scratch')
            ->latest()
            ->get()
            ->map(function ($h) {

                // マイル数（正の値で扱う）
                $miles = (int) $h->miles;

                // タイトルを結果に応じて分岐
                if ($miles > 0) {
                    $title = "🎉 スクラッチ当たり（+{$miles} マイル）";
                } else {
                    $title = "😢 スクラッチはずれ";
                }

                return [
                    'type'       => 'scratch',
                    'title'      => $title,
                    'created_at' => $h->created_at,
                    'expires_at' => null,
                    'icon'       => '🪙',
                    'miles'      => $miles,
                ];
            });


        //ガチャとスクラッチ履歴を統合して新しい順
        $histories = $gachaHistories
            ->merge($scratchHistories)
            ->sortByDesc('created_at');

        return view('rewards.history', compact('histories'));
    }
}
