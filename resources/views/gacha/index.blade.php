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

                   animate-fade-in">
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
                🧪 テスト用 +100 マイル
            </button>
        </form>

        {{-- 🧪 開発環境のみ：テスト用スクラッチポイント --}}
        <form method="POST" action="{{ route('debug.add-scratch-points') }}">
            @csrf
            <button
                type="submit"
                class="w-full py-2 rounded-xl bg-amber-400 text-white font-semibold
               hover:bg-amber-500 transition">
                🧪 テスト用 +10 スクラッチpt
            </button>
        </form>
        @endif

        {{-- ============================= --}}
        {{-- ガチャ / スクラッチ --}}
        {{-- ============================= --}}
        <div class="space-y-4 pt-2">

            {{-- 🎰 ガチャ --}}
            <form method="POST" action="{{ route('rewards.play.gacha') }}">
                @csrf
                <button
                    @disabled(!$canDrawGacha)
                    class="w-full py-3 rounded-xl font-bold text-lg shadow-md transition
                        {{ $canDrawGacha
                            ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white hover:opacity-90'
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">
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
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">
                    🪙 スクラッチを削る
                </button>

                <p class="text-xs mt-1 text-slate-600">
                    消費：{{ $scratchCost }} pt
                    @unless($canDrawScratch)
                    <span class="text-red-500">
                        （あと {{ max(0, $scratchCost - $points) }} pt 不足）
                    </span>
                    @endunless
                </p>

                <p class="text-[11px] text-slate-400 mt-1">
                    ※ スクラッチはミッションポイントを消費します
                </p>
            </form>

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