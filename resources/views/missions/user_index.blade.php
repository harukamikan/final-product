@extends('layouts.app')

@section('content')
{{-- ミッション達成モーダル --}}
@if(session('achievementData'))
    @include('components.mission-completion-modal', ['achievementData' => session('achievementData')])
@endif

{{-- 進捗トースト（未完了の場合） --}}
@if(session('progressData'))
    @include('components.mission-progress-toast', ['progressData' => session('progressData')])
@endif

<div class="max-w-5xl mx-auto px-4 py-8 space-y-8">

    {{-- ----- マイル残高 & スクラッチポイントカード（2カラム） ----- --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- マイル残高カード --}}
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-3xl p-5 shadow-md">
            @include('components.animated-miles-display', [
                'currentMiles' => $totalMiles,
                'size' => 'medium',
                'showIcon' => true,
                'icon' => '🎯',
                'label' => '現在のマイル残高'
            ])
        </div>
        {{-- スクラッチポイントカード --}}
        <div class="bg-gradient-to-r from-amber-400 to-amber-500 text-white rounded-3xl p-5 shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">スクラッチポイント</p>
                    <p class="text-4xl font-bold mt-2">{{ Auth::user()->personal_mission_points ?? 0 }}</p>
                    <p class="text-xs opacity-75 mt-1">個人ミッション達成で獲得</p>
                </div>
                <div class="text-6xl opacity-80">🎟️</div>
            </div>
        </div>
    </div>

    {{-- ----- タブ切り替え ----- --}}
   <div class="flex gap-4 text-sm font-medium">
    <a href="{{ route('missions.index') }}"
       class="px-4 py-2 rounded-xl {{ request()->routeIs('missions.index') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        進行中のミッション
    </a>

    <a href="{{ route('missions.completed') }}"
        class="px-4 py-2 rounded-xl {{ request()->routeIs('missions.completed') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        完了したミッション
    </a>

    <a href="{{ route('missions.personal') }}"
        class="px-4 py-2 rounded-xl {{ request()->routeIs('missions.personal') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        個人ミッション
    </a>
    </div>

    {{-- フラッシュメッセージ --}}
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 shadow-sm">
            <p class="font-medium">{{ session('success') }}</p>

            @if ($earnedThisTime > 0)
                <p class="text-sm mt-1 font-semibold">＋ {{ $earnedThisTime }} mile 獲得！</p>
            @endif
        </div>
    @endif

    {{-- ----- 進行中が0件のとき ----- --}}
    @if ($missions->isEmpty())
        <div class="text-center p-10 bg-white rounded-3xl shadow-sm">
            <div class="text-5xl mb-4">🎉</div>
            <p class="text-xl font-semibold">すべてのミッションを達成しました！</p>
            <p class="text-gray-500 mt-2">新しいミッションが追加されるまでお待ちください。</p>
        </div>
    @endif



    {{-- ----- ミッションカード一覧 ----- --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($missions as $mission)
            @php
                $userMission = $mission->userMissions->first();
            @endphp

            @include('missions._mission_card', [
                'mission' => $mission,
                'userMission' => $userMission,
                'isCompleted' => false
            ])
        @endforeach
    </div>
</div>
@endsection
