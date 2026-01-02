@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto text-center space-y-6">

    {{-- タイトル --}}
    <h1 class="text-2xl font-bold">🎰 ガチャ</h1>

    {{-- 現在のマイル表示 --}}
    <p class="text-sm text-gray-700">
        現在のマイル：<span class="font-semibold">{{ $totalMiles }}</span>
    </p>

    {{-- 開発環境のみ：テスト用マイル付与 --}}
    @if(app()->environment('local'))
    <form method="POST" action="{{ route('debug.add-miles') }}">
        @csrf
        <button
            type="submit"
            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">
            🧪 テスト用 +1000 マイル
        </button>
    </form>
    @endif

    {{-- ============================= --}}
    {{-- ガチャ / スクラッチ実行判定 --}}
    {{-- ============================= --}}
    @if($canDrawGacha && $hasActiveGachaReward)

    <div class="space-y-4">

        {{-- ガチャ --}}
        <form method="POST" action="{{ route('rewards.play.gacha') }}">
            @csrf
            <button class="btn-primary">ガチャを引く</button>
        </form>

        {{-- スクラッチ --}}
        <form method="POST" action="{{ route('rewards.play.scratch') }}">
            @csrf
            <button class="btn-primary">スクラッチを削る</button>
        </form>
    </div>

    {{-- マイル不足 --}}
    @elseif(!$canDrawGacha)

    <p class="text-gray-500">
        マイルが不足しています。<br>
        ミッションを達成してマイルを獲得しましょう！
    </p>

    {{-- 報酬が存在しない --}}
    @else

    <p class="text-gray-500">
        現在、ガチャに配布中の報酬がありません。<br>
        管理者が報酬を設定するまでお待ちください。
    </p>

    @endif

</div>
@endsection