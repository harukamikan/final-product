@extends('layouts.app')

@section('content')
{{-- ミッション達成モーダル --}}
@include('components.mission-completion-modal', ['achievementData' => $achievementData ?? []])

<div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

    {{-- 完了メッセージ & マイル --}}
    <div class="rounded-3xl bg-emerald-50 border border-emerald-100 px-5 py-4 flex justify-between items-center">
        <div>
            <p class="text-sm text-emerald-700 font-medium">
                Connpassイベントを登録しました 🎉
            </p>
            @if (($achievementData['earned_miles'] ?? 0) > 0)
                <p class="text-xs text-emerald-700 mt-1">
                    このミッションで <span class="font-bold">{{ $achievementData['earned_miles'] }} mile</span> を獲得しました。
                </p>
            @endif
        </div>
        <div class="text-3xl">🎤</div>
    </div>

    {{-- Connpassイベントの概要 --}}
    <div class="rounded-3xl border bg-white shadow-sm px-5 py-6 space-y-4">
        <div>
            <p class="text-xs text-gray-500">紐づくミッション</p>
            <p class="text-sm font-semibold text-gray-900">
                {{ $mission->title }}
            </p>
        </div>

        <div class="border-t pt-4 space-y-3">
            <div>
                <p class="text-xs text-gray-500 mb-1">イベント名</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ $eventData['title'] }}
                </p>
            </div>

            @if (!empty($eventData['catch']))
                <div>
                    <p class="text-xs text-gray-500 mb-1">キャッチコピー</p>
                    <p class="text-sm text-gray-700">
                        {{ $eventData['catch'] }}
                    </p>
                </div>
            @endif

            @if (!empty($eventData['description']))
                <div>
                    <p class="text-xs text-gray-500 mb-1">イベント概要</p>
                    <div class="text-sm bg-gray-50 rounded-2xl p-3 overflow-auto max-h-64">
                        {!! nl2br(e(Str::limit($eventData['description'], 500))) !!}
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                @if ($eventData['started_at'])
                    <span>開始: 
                        <span class="font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($eventData['started_at'])->format('Y/m/d H:i') }}
                        </span>
                    </span>
                @endif
                @if ($eventData['ended_at'])
                    <span>終了: 
                        <span class="font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($eventData['ended_at'])->format('Y/m/d H:i') }}
                        </span>
                    </span>
                @endif
            </div>

            @if (!empty($eventData['place']))
                <div>
                    <p class="text-xs text-gray-500 mb-1">開催場所</p>
                    <p class="text-sm text-gray-700">
                        {{ $eventData['place'] }}
                        @if (!empty($eventData['address']))
                            <br><span class="text-xs text-gray-500">{{ $eventData['address'] }}</span>
                        @endif
                    </p>
                </div>
            @endif

            <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                @if (isset($eventData['limit']))
                    <span>定員: <span class="font-semibold text-gray-800">{{ $eventData['limit'] }}名</span></span>
                @endif
                @if (isset($eventData['accepted']))
                    <span>参加者: <span class="font-semibold text-gray-800">{{ $eventData['accepted'] }}名</span></span>
                @endif
                @if (isset($eventData['waiting']))
                    <span>補欠: <span class="font-semibold text-gray-800">{{ $eventData['waiting'] }}名</span></span>
                @endif
            </div>

            @if (!empty($eventData['owner_display_name']))
                <div>
                    <p class="text-xs text-gray-500 mb-1">主催者</p>
                    <p class="text-sm text-gray-700">
                        {{ $eventData['owner_display_name'] }}
                    </p>
                </div>
            @endif

            @if (!empty($eventData['hash_tag']))
                <div>
                    <p class="text-xs text-gray-500 mb-1">ハッシュタグ</p>
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs">
                        #{{ $eventData['hash_tag'] }}
                    </span>
                </div>
            @endif

            <div>
                <a href="{{ $eventData['event_url'] }}"
                   target="_blank"
                   class="inline-flex items-center text-xs text-indigo-600 hover:underline">
                    Connpassでイベントを開く
                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M14 3h7m0 0v7m0-7L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- 戻るボタン --}}
    <div class="flex justify-end">
        <a href="{{ route('missions.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-xl bg-gray-900 text-white text-sm font-medium hover:bg-black">
            ミッション一覧に戻る
        </a>
    </div>

</div>
@endsection
