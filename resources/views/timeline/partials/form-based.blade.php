{{-- フォームベースのミッションカード（イベント企画・登壇・資格取得） --}}
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

    {{-- 実施日 --}}
    @if (!empty($payload['occurred_on']))
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="font-mono">{{ $payload['occurred_on'] }}</span>
        </div>
    @endif

    {{-- 詳細 --}}
    @if (!empty($payload['details']))
        <div class="text-sm">
            {{-- プレビュー --}}
            <div x-show="!expanded">
                <p class="text-gray-700 line-clamp-3">{{ $payload['details'] }}</p>
                @if (strlen($payload['details']) > 100)
                    <button @click="expanded = true" 
                            class="mt-2 text-indigo-600 hover:text-indigo-800 font-semibold text-xs">
                        もっと見る
                    </button>
                @endif
            </div>

            {{-- 展開表示 --}}
            @if (strlen($payload['details']) > 100)
                <div x-show="expanded" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                        <p class="whitespace-pre-wrap text-gray-800 leading-relaxed">{{ $payload['details'] }}</p>
                        <button @click="expanded = false" 
                                class="mt-2 text-indigo-600 hover:text-indigo-800 text-xs font-semibold">
                            閉じる
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- 証跡URL --}}
    @if (!empty($payload['evidence_url']))
        <div class="flex items-center gap-2">
            <a href="{{ $payload['evidence_url'] }}" target="_blank" 
               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-semibold hover:bg-indigo-200 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                証跡リンク
            </a>
        </div>
    @endif

</div>
