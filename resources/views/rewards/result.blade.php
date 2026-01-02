@extends('layouts.app')

@section('content')
@php
$via = $via ?? 'gacha';
@endphp

<div class="min-h-screen flex items-center justify-center">
    <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-lg p-8">

        {{-- ×ボタン --}}
        <a
            href="{{ route('rewards.gacha') }}"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">
            ×
        </a>


        {{-- タイトル --}}
        <h2 class="text-xl font-bold text-center mb-6">
            🎉 {{ $via === 'scratch' ? 'スクラッチ結果' : 'ガチャ結果' }} 🎉
        </h2>

        {{-- 報酬カード --}}
        <div class="border-2 border-dashed border-indigo-400 rounded-xl p-6 text-center">
            <p class="text-sm text-gray-500 mb-2">獲得した報酬</p>
            <p class="text-2xl font-semibold text-indigo-600">
                {{ $reward->name }}
            </p>
        </div>

    </div>
</div>
@endsection