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
    {{-- 個人ミッション追加ボタン --}}
    <div class="flex justify-end">
        <a href="{{ route('personal-missions.create') }}"
        class="px-6 py-2 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
            ➕ 個人ミッションを追加
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
                $progress = $mission->progress_count ?? 0;
                $required = $mission->required_count;
                $ratio = min(100, intval($progress / max(1, $required) * 100));
                
                // ミッション種別を取得
                $type = getMissionType($mission);
                $icon = getMissionIcon($type);
                $actionLabel = getMissionActionLabel($type);
                $colors = getMissionColorClasses($type);
                
                $adminSetting = \App\Models\AdminSetting::first();
                $canManage = $mission->user_id && 
                    $adminSetting && 
                    $adminSetting->personal_mission_edit_start && 
                    $adminSetting->personal_mission_edit_end &&
                    now()->between($adminSetting->personal_mission_edit_start, $adminSetting->personal_mission_edit_end);
            @endphp

            <div class="rounded-3xl border-l-4 {{ $colors['border'] }} bg-white px-5 py-6 shadow-sm space-y-4">
                {{-- タイトルエリア + 円形プログレス --}}
                <div class="flex justify-between items-start gap-4">
                    {{-- 左側: アイコン + タイトル --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-3xl leading-none">{{ $icon }}</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $colors['badge'] }}">
                                {{ $actionLabel }}
                            </span>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ $mission->title }}</h2>
                        @if($mission->description)
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $mission->description }}</p>
                        @endif
                        {{-- サイクル表示 --}}
                        @if($mission->cycle_type === 'weekly')
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    🔄 週間目標
                                    @if($mission->cycle_streak > 0)
                                        <span class="ml-1 font-bold">{{ $mission->cycle_streak }}週達成中！</span>
                                    @endif
                                </span>
                            </div>
                        @elseif($mission->cycle_type === 'monthly')
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                    📅 月間目標
                                    @if($mission->cycle_streak > 0)
                                        <span class="ml-1 font-bold">{{ $mission->cycle_streak }}ヶ月達成中！</span>
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- 右側: 円形プログレス --}}
                    <div class="flex-shrink-0">
                        @include('components.circular_progress', [
                            'current' => $progress,
                            'required' => $required,
                            'colorClass' => $colors['ring'],
                            'size' => 64
                        ])
                    </div>
                </div>

                {{-- 下部エリア: ボタン --}}
                <div class="pt-3 border-t flex justify-between items-center">
                    <div class="flex gap-2">
                        @if ($canManage)
                            <a href="{{ route('personal-missions.edit', $mission) }}"
                               onclick="event.stopPropagation()"
                               class="px-3 py-1 rounded-lg bg-amber-500 text-white text-xs font-medium hover:bg-amber-600 transition">
                                ✏️ 編集
                            </a>
                            
                            <form action="{{ route('personal-missions.destroy', $mission) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="event.stopPropagation(); return confirm('削除してもいいですか？')"
                                        class="px-3 py-1 rounded-lg bg-red-500 text-white text-xs font-medium hover:bg-red-600 transition">
                                    🗑️ 削除
                                </button>
                            </form>
                        @endif
                    </div>
                    
                    {{-- 個人ミッションのみ「完了 +1」ボタン --}}
                    @if ($mission->user_id)
                        @if (!$mission->linked_category)
                            <form action="{{ route('personal-missions.complete', $mission) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        onclick="event.stopPropagation()"
                                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-medium hover:from-indigo-700 hover:to-indigo-800 transition shadow-sm">
                                    完了 +1
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
