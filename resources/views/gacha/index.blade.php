@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div
        class="w-full max-w-md bg-white rounded-3xl shadow-xl p-8 space-y-6 text-center relative">

        {{-- 📜 報酬履歴（右上・アニメーション付き） --}}
        <a
            href="{{ route('rewards.history') }}"
            class="absolute top-4 right-4
                   inline-flex items-center gap-1
                   px-4 py-2 text-xs font-bold
                   text-indigo-600
                   bg-white/90 backdrop-blur
                   border border-indigo-200
                   rounded-full shadow-md

                   transition-all duration-300 ease-out
                   hover:-translate-y-1 hover:shadow-lg hover:bg-indigo-50

                   animate-fade-in"
        >
            📜 履歴
        </a>

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
                class="w-full py-2 rounded-xl bg-red-500 text-white font-semibold
                       hover:bg-red-600 transition">
                🧪 テスト用 +1000 マイル
            </button>
        </form>
        @endif

        {{-- ============================= --}}
        {{-- ガチャ / スクラッチ --}}
        {{-- ============================= --}}
        @if($hasActiveReward)

        <div class="space-y-4 pt-2">

            {{-- 🎰 ガチャ --}}
            <form method="POST" action="{{ route('rewards.play.gacha') }}">
                @csrf
                <button
                    @disabled(!$canDrawGacha)
                    class="w-full py-3 rounded-xl font-bold text-lg shadow-md transition
                        {{ $canDrawGacha
                            ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white hover:opacity-90'
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                >
                    🎰 ガチャを引く
                </button>

                <p class="text-xs mt-1 text-slate-600">
                    消費：{{ $gachaCost }} マイル
                    @unless($canDrawGacha)
                        <span class="text-red-500">
                            （あと {{ max(0, $gachaCost - $totalMiles) }} マイル不足）
                        </span>
                    @endunless
                </p>
            </form>

            {{-- 🪙 スクラッチ --}}
            <form method="POST" action="{{ route('rewards.play.scratch') }}">
                @csrf
                <button
                    @disabled(!$canDrawScratch)
                    class="w-full py-3 rounded-xl font-bold text-lg shadow-md transition
                        {{ $canDrawScratch
                            ? 'bg-gradient-to-r from-amber-400 to-orange-400 text-white hover:opacity-90'
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                >
                    🪙 スクラッチを削る
                </button>

                <p class="text-xs mt-1 text-slate-600">
                    消費：{{ $scratchCost }} マイル
                    @unless($canDrawScratch)
                        <span class="text-red-500">
                            （あと {{ max(0, $scratchCost - $totalMiles) }} マイル不足）
                        </span>
                    @endunless
                </p>
            </form>

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

{{-- ===== フェードインアニメーション定義 ===== --}}
<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeInUp 0.4s ease-out forwards;
}
</style>
@endsection
