{{-- フォームベースのミッションカード（イベント企画・登壇・資格取得） --}}
{{-- 共通フォーマット対応 --}}
@php
    $payload = $event->payload;
@endphp

<div x-data="{ expanded: false }">

    {{-- タイトル --}}
    <div>
        <h3 class="text-xl font-bold text-gray-900 leading-tight">
            {{ $payload['title'] ?? 'タイトルなし' }}
        </h3>
    </div>

    {{-- 開催日時 --}}
    @if (!empty($payload['started_at']))
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="font-mono">
                {{ \Carbon\Carbon::parse($payload['started_at'])->format('Y/m/d H:i') }}
                @if (!empty($payload['ended_at']))
                    〜 {{ \Carbon\Carbon::parse($payload['ended_at'])->format('H:i') }}
                @endif
            </span>
        </div>
    @endif

    {{-- 開催場所 --}}
    @if (!empty($payload['place']) || !empty($payload['address']))
        <div class="flex items-start gap-2 text-sm text-gray-600">
            <svg class="w-4 h-4 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <div>
                @if (!empty($payload['place']))
                    <p>{{ $payload['place'] }}</p>
                @endif
                @if (!empty($payload['address']))
                    <p class="text-xs text-gray-500">{{ $payload['address'] }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- 詳細 --}}
    @if (!empty($payload['description']))
        <div class="text-sm">
            {{-- プレビュー --}}
            <div x-show="!expanded">
                <p class="text-gray-700 line-clamp-3">{!! nl2br(e(Str::limit(strip_tags($payload['description']), 150))) !!}</p>
                @if (strlen(strip_tags($payload['description'])) > 150)
                    <button @click="expanded = true" 
                            class="mt-2 text-indigo-600 hover:text-indigo-800 font-semibold text-xs">
                        もっと見る
                    </button>
                @endif
            </div>

            {{-- 展開表示 --}}
            @if (strlen(strip_tags($payload['description'])) > 150)
                <div x-show="expanded" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 max-h-96 overflow-y-auto">
                        <p class="whitespace-pre-wrap text-gray-800 leading-relaxed">{!! nl2br(e(strip_tags($payload['description']))) !!}</p>
                        <button @click="expanded = false" 
                                class="mt-2 text-indigo-600 hover:text-indigo-800 text-xs font-semibold">
                            閉じる
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Connpass固有情報（参加者数など） --}}
    @if (!empty($payload['additional']))
        @php $additional = $payload['additional']; @endphp
        
        {{-- キャッチコピー --}}
        @if (!empty($additional['catch']))
            <div class="text-sm text-gray-600 italic">
                {{ $additional['catch'] }}
            </div>
        @endif
        
        {{-- 参加者情報 --}}
        @if (isset($additional['accepted']) || isset($additional['limit']) || isset($additional['waiting']))
            <div class="flex items-center gap-4 text-sm text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @if (isset($additional['accepted']) && isset($additional['limit']))
                    <span><span class="font-semibold text-gray-800">{{ $additional['accepted'] }}</span> / {{ $additional['limit'] }}名</span>
                @elseif (isset($additional['accepted']))
                    <span>参加者: <span class="font-semibold text-gray-800">{{ $additional['accepted'] }}名</span></span>
                @elseif (isset($additional['limit']))
                    <span>定員: <span class="font-semibold text-gray-800">{{ $additional['limit'] }}名</span></span>
                @endif
                
                @if (isset($additional['waiting']) && $additional['waiting'] > 0)
                    <span class="text-amber-600">補欠: {{ $additional['waiting'] }}名</span>
                @endif
            </div>
        @endif
        
        {{-- ハッシュタグ --}}
        @if (!empty($additional['hash_tag']))
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                    #{{ $additional['hash_tag'] }}
                </span>
            </div>
        @endif
    @endif

    {{-- イベントURL --}}
    @if (!empty($payload['url']))
        <div class="flex items-center gap-2">
            <a href="{{ $payload['url'] }}" target="_blank" 
               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-semibold hover:bg-indigo-200 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                @if (($payload['source'] ?? '') === 'connpass')
                    Connpassで見る
                @else
                    イベントURL
                @endif
            </a>
        </div>
    @endif

</div>
