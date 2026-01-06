@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl p-8 space-y-6 text-center">

        {{-- 🎰 タイトル --}}
        <h1 class="text-3xl font-extrabold text-indigo-600 tracking-wide">
            🎰 ガチャ
        </h1>

        {{-- 💎 現在のマイル --}}
        <div class="bg-indigo-50 rounded-xl py-4">
            <p class="text-sm text-slate-600">現在のマイル</p>
            <p class="text-3xl font-bold text-indigo-700">
                {{ number_format($totalMiles) }}
            </p>
        </div>

        {{-- 🧪 開発環境のみ：テスト用マイル --}}
        @if(app()->environment('local'))
        <form method="POST" action="{{ route('debug.add-miles') }}">
            @csrf
            <button
                type="submit"
                class="w-full py-2 rounded-xl bg-red-500 text-white font-semibold hover:bg-red-600 transition">
                🧪 テスト用 +1000 マイル
            </button>
        </form>
        @endif

        {{-- ============================= --}}
        {{-- ガチャ / スクラッチ実行判定 --}}
        {{-- ============================= --}}
        @if($canDrawGacha && $hasActiveGachaReward)

        <div class="space-y-4 pt-2">

            {{-- 🎰 ガチャ --}}
            <form method="POST" action="{{ route('rewards.play.gacha') }}">
                @csrf
                <button
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500
                           text-white font-bold text-lg hover:opacity-90 transition shadow-md">
                    🎰 ガチャを引く
                </button>
            </form>

            {{-- 🪙 スクラッチ --}}
            <form method="POST" action="{{ route('rewards.play.scratch') }}">
                @csrf
                <button
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-amber-400 to-orange-400
                           text-white font-bold text-lg hover:opacity-90 transition shadow-md">
                    🪙 スクラッチを削る
                </button>
            </form>
        </div>

        {{-- ⚠️ マイル不足 --}}
        @elseif(!$canDrawGacha)

        <div class="bg-slate-50 rounded-xl p-4 text-slate-600 text-sm leading-relaxed">
            マイルが不足しています。<br>
            ミッションを達成してマイルを獲得しましょう！
        </div>

        {{-- 📦 報酬なし --}}
        @else

        <div class="bg-slate-50 rounded-xl p-4 text-slate-600 text-sm leading-relaxed">
            現在、ガチャに配布中の報酬がありません。<br>
            管理者が報酬を設定するまでお待ちください。
        </div>

        @endif

    </div>
</div>
@endsection
