{{-- resources/views/missions/user_index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-8">

    {{-- ヘッダー --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">マイミッション</h1>
            <p class="text-sm text-gray-500 mt-1">
                日々のアウトプットでミッションを達成して、マイルを貯めよう。
            </p>
        </div>
    </div>

    {{-- サマリーカード --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border bg-white px-4 py-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">保有マイル</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $totalMiles }}</p>
            </div>
            <div class="rounded-full bg-yellow-50 p-3">
                <span class="text-yellow-500 text-lg">💰</span>
            </div>
        </div>

        <div class="rounded-2xl border bg-white px-4 py-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">進行中のミッション</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $activeCount }}</p>
            </div>
            <div class="rounded-full bg-indigo-50 p-3">
                <span class="text-indigo-500 text-lg">🔥</span>
            </div>
        </div>

        <div class="rounded-2xl border bg-white px-4 py-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">達成済み</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $completedCount }}</p>
            </div>
            <div class="rounded-full bg-emerald-50 p-3">
                <span class="text-emerald-500 text-lg">✅</span>
            </div>
        </div>
    </div>

    {{-- フィルタ（タブ風） --}}
    <div class="rounded-2xl border bg-white shadow-sm px-2 py-2 flex items-center justify-between">
        <div class="inline-flex items-center bg-gray-100 rounded-xl p-1 text-xs font-medium">
            @php
                $tabs = [
                    'all'       => 'すべて',
                    'active'    => '進行中',
                    'completed' => '達成済み',
                ];
            @endphp

            @foreach($tabs as $value => $label)
                <a href="{{ route('missions.index', ['filter' => $value]) }}"
                   class="px-3 py-1 rounded-lg transition
                        {{ $filter === $value ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- フラッシュメッセージ --}}
    @if (session('success'))
        <div class="rounded-2xl border border-green-100 bg-green-50 px-4 py-3 text-sm text-green-800 flex items-start gap-2">
            <svg class="w-5 h-5 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ミッション一覧 --}}
    <div class="space-y-3">
        @php
            $filtered = $missions->filter(function ($mission) use ($filter) {
                $um = $mission->userMissions->first();
                $completed = $um && $um->completed_at;

                return match ($filter) {
                    'active'    => !$completed,
                    'completed' => $completed,
                    default     => true,
                };
            });
        @endphp

        @forelse ($filtered as $mission)
            @php
                $userMission = $mission->userMissions->first();
                $progress    = $userMission->progress_count ?? 0;
                $required    = max(1, $mission->required_count ?? 1);
                $percent     = min(100, (int) round($progress / $required * 100));

                $completed   = $userMission && $userMission->completed_at;
                $isManual    = str_starts_with($mission->trigger_type, 'manual');
                $isQiita     = $mission->key === 'write_qiita_article'; // お好みで条件変更
            @endphp

            <div class="rounded-2xl border bg-white px-4 py-4 md:px-5 md:py-5 shadow-sm flex flex-col gap-4 md:flex-row md:items-center md:justify-between hover:border-indigo-200 hover:shadow-md transition">
                {{-- 左側：タイトル + 説明 + タグ --}}
                <div class="flex-1 space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-sm font-semibold text-gray-900">
                            {{ $mission->title }}
                        </h2>

                        {{-- 種類バッジ --}}
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                            @if($isQiita)
                                bg-emerald-50 text-emerald-700
                            @elseif($mission->key === 'speak_at_event')
                                bg-indigo-50 text-indigo-700
                            @elseif($mission->key === 'organize_event')
                                bg-orange-50 text-orange-700
                            @elseif($mission->key === 'get_certification')
                                bg-purple-50 text-purple-700
                            @else
                                bg-gray-100 text-gray-700
                            @endif
                        ">
                            @if($isQiita)
                                Qiita記事
                            @elseif($mission->key === 'speak_at_event')
                                イベント登壇
                            @elseif($mission->key === 'organize_event')
                                イベント企画
                            @elseif($mission->key === 'get_certification')
                                資格取得
                            @else
                                その他
                            @endif
                        </span>

                        {{-- ステータスバッジ --}}
                        @if ($completed)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-medium">
                                ✅ 達成済み
                            </span>
                        @elseif($progress > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[11px] font-medium">
                                🔥 進行中
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[11px] font-medium">
                                ⏳ 未着手
                            </span>
                        @endif
                    </div>

                    @if($mission->description)
                        <p class="text-xs text-gray-500">
                            {{ $mission->description }}
                        </p>
                    @endif

                    {{-- 進捗バー --}}
                    <div class="space-y-1 mt-1">
                        <div class="flex items-baseline justify-between text-xs text-gray-500">
                            <span>進捗</span>
                            <span>{{ $progress }} / {{ $required }}</span>
                        </div>
                        <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full
                                        {{ $completed ? 'bg-emerald-500' : 'bg-indigo-500' }}"
                                 style="width: {{ $percent }}%;"></div>
                        </div>
                    </div>
                </div>

                {{-- 右側：報酬 & アクションボタン --}}
                <div class="flex flex-col items-end gap-2 min-w-[150px]">
                    {{-- 報酬マイル --}}
                    <div class="text-xs text-gray-500 text-right">
                        報酬
                        <span class="ml-1 text-sm font-semibold text-gray-900">
                            {{ $mission->reward_miles }}
                        </span>
                        <span class="text-[11px] text-gray-400">mile</span>
                    </div>

                    {{-- アクションボタン --}}
                    @if ($completed && !$mission->repeatable)
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs">
                            すでに達成しています
                        </span>
                    @else
                        <div class="flex flex-wrap justify-end gap-2">

                            {{-- Qiitaミッション：URL登録へ --}}
                            @if ($isQiita)
                                <a href="{{ route('missions.blog-url.form') ?? '#' }}"  {{-- 実際のルート名に合わせて修正 --}}
                                   class="inline-flex items-center px-3 py-1.5 rounded-full bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700">
                                    URLを登録する
                                </a>

                            {{-- manual系ミッション：達成ボタン --}}
                            @elseif ($isManual)
                                <form method="POST" action="{{ route('missions.complete', $mission) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700">
                                        ミッション達成
                                    </button>
                                </form>
                            @endif

                            {{-- 繰り返しミッションで達成済みの場合の再チャレンジメッセージなども追加可 --}}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border bg-white px-4 py-8 text-center text-sm text-gray-500">
                表示できるミッションがありません。
            </div>
        @endforelse
    </div>
</div>
@endsection
