@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-12">

    <div class="bg-white rounded-2xl shadow-xl p-8 text-center space-y-6">

        <h1 class="text-2xl font-bold text-gray-800">
            🎉 ガチャ結果 🎉
        </h1>

        {{-- 報酬表示 --}}
        <div class="border-4 border-dashed border-indigo-400 rounded-xl p-6">
            <p class="text-sm text-gray-500 mb-2">
                獲得した報酬
            </p>

            <p class="text-3xl font-extrabold text-indigo-600">
                {{ $reward->name }}
            </p>
        </div>

        {{-- 説明 --}}
        @if($reward->description)
            <p class="text-gray-600">
                {{ $reward->description }}
            </p>
        @endif

        {{-- ボタン --}}
        <div class="flex justify-center gap-4 pt-6">
            <a href="{{ route('dashboard') }}"
               class="px-6 py-3 rounded-xl bg-gray-200 text-gray-800 hover:bg-gray-300">
                ダッシュボードへ
            </a>

            <a href="{{ route('ranking.index') }}"
               class="px-6 py-3 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
                ランキングを見る
            </a>
        </div>

    </div>

</div>
@endsection
