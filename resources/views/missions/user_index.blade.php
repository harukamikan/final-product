@extends('layouts.app')

@section('content')
{{-- ミッション達成モーダル --}}
@if(session('achievementData'))
    {{-- DEBUG: データ確認 --}}
    @php
        $achievementData = session('achievementData');
        \Log::info('Achievement Data:', $achievementData);
    @endphp
    
    @if(isset($achievementData['mission_completed']) && $achievementData['mission_completed'])
        <!-- Modal should display -->
        @include('components.mission-completion-modal', ['achievementData' => $achievementData])
    @else
        <!-- DEBUG: mission_completed is false or missing -->
        <div style="position: fixed; top: 10px; right: 10px; background: red; color: white; padding: 10px; z-index: 9999;">
            Debug: mission_completed = {{ json_encode($achievementData['mission_completed'] ?? 'NOT SET') }}
        </div>
    @endif
@else
    <!-- DEBUG: No achievementData in session -->
    <!--<div style="position: fixed; top: 10px; right: 10px; background: orange; color: white; padding: 10px; z-index: 9999;">
        Debug: No achievementData in session
    </div>-->
@endif

{{-- 進捗トースト（未完了の場合） --}}
@if(session('progressData'))
    @include('components.mission-progress-toast', ['progressData' => session('progressData')])
@endif

<div class="max-w-5xl mx-auto px-4 py-8 space-y-8">

    {{-- ----- マイル残高カード ----- --}}
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-3xl p-6 shadow-md">
        @include('components.animated-miles-display', [
            'currentMiles' => $totalMiles,
            'size' => 'medium',
            'showIcon' => true,
            'icon' => '🎯',
            'label' => '現在のマイル残高'
        ])
    </div>
    {{-- ----- スクラッチポイントカード ----- --}}
    <div class="bg-gradient-to-r from-amber-400 to-amber-500 text-white rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium opacity-90">スクラッチポイント</p>
                <p class="text-4xl font-bold mt-2">{{ Auth::user()->personal_mission_points ?? 0 }}</p>
                <p class="text-xs opacity-75 mt-1">個人ミッション達成で獲得</p>
            </div>
            <div class="text-6xl opacity-80">🎟️</div>
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($missions as $mission)
            @php
                $userMission = $mission->userMissions->first();
                $progress = $userMission->progress_count ?? 0;
                $required = $mission->required_count;
                $ratio = min(100, intval($progress / max(1, $required) * 100));
            @endphp

            <div class="rounded-3xl border bg-white px-5 py-6 shadow-sm space-y-4">

                {{-- タイトル + マイル --}}
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $mission->title }}</h2>
                        <p class="text-xs text-gray-500 mt-1">{{ $mission->description }}</p>
                    </div>

                    <div class="text-right">
                        <p class="text-xs text-gray-500">報酬</p>
                        <p class="text-lg font-bold text-indigo-600">{{ $mission->reward_miles }} mile</p>
                    </div>
                </div>

                {{-- 進捗バー --}}
                <div>
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>進捗</span>
                        <span>{{ $progress }} / {{ $required }}</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full transition-all" style="width: {{ $ratio }}%;"></div>
                    </div>
                </div>

                {{-- ボタン --}}
                <div class="pt-3 border-t flex justify-end">
                    @if ($mission->trigger_type === 'tech_blog_posted')
                        <a href="{{ route('missions.blog-url.form') }}"
                           class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                            技術ブログのURLを送信
                        </a>
                    @elseif ($mission->trigger_type === 'google_form_submitted')
                        <a href="{{ route('missions.form.create', ['mission' => $mission->id]) }}"
                           class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                            GoogleフォームのURLを送信
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
