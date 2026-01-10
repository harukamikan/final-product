{{-- Qiita記事カード --}}
@php
    $payload = $event->payload;
    $hasSummary = !empty($payload['summary']);
    $displayTags = array_slice($payload['tags'] ?? [], 0, 3);
    $remainingTagsCount = count($payload['tags'] ?? []) - count($displayTags);
    $summaryPreview = $hasSummary ? Str::limit($payload['summary'], 80) : '';
@endphp

<div x-data="{ 
    expanded: false,
    liked: false,
    likeCount: {{ $payload['likes_count'] ?? 0 }}
}">

    {{-- タイトル --}}
    <div>
        <a href="{{ $payload['url'] ?? '#' }}" target="_blank"
           class="block text-xl font-bold text-gray-900 hover:text-indigo-600 transition-colors leading-tight line-clamp-2">
            {{ $payload['title'] ?? 'タイトルなし' }}
        </a>
    </div>

    {{-- タグ --}}
    @if (!empty($displayTags))
        <div class="flex flex-wrap gap-1.5 items-center">
            @foreach ($displayTags as $tag)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-700 text-xs font-medium border border-indigo-100">
                    #{{ $tag }}
                </span>
            @endforeach
            @if ($remainingTagsCount > 0)
                <span class="text-xs text-gray-400 font-medium">+{{ $remainingTagsCount }}</span>
            @endif
        </div>
    @endif

    {{-- AI要約 --}}
    @if ($hasSummary)
        <div class="text-sm">
            {{-- プレビュー --}}
            <div x-show="!expanded" class="flex items-start gap-2">
                <div class="flex-1">
                    <span class="inline-flex items-center gap-1 text-indigo-600 font-semibold text-xs mb-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        AI要約
                    </span>
                    <p class="text-gray-700">{{ $summaryPreview }}</p>
                </div>
                <button @click="expanded = true" 
                        class="shrink-0 text-indigo-600 hover:text-indigo-800 font-semibold text-xs px-2 py-1 rounded-md hover:bg-indigo-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            {{-- 展開表示 --}}
            <div x-show="expanded" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <div class="rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center gap-1 text-indigo-700 font-bold text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            AI要約
                        </span>
                        <button @click="expanded = false" 
                                class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                    </div>
                    <p class="whitespace-pre-wrap text-gray-800 leading-relaxed">{{ $payload['summary'] }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gradient-to-r from-amber-50 to-orange-50 text-amber-700 text-xs font-semibold border border-amber-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            AI要約未生成
        </div>
    @endif

    {{-- フッター：いいね + 投稿日 --}}
    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
        <button @click="liked = !liked; likeCount += liked ? 1 : -1" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all duration-200"
                :class="liked ? 'bg-pink-100 text-pink-600' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            <svg class="w-4 h-4" :class="liked && 'fill-current'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <span class="font-bold text-sm" x-text="likeCount"></span>
        </button>
        
        @if (!empty($payload['posted_at']))
            <time class="text-xs text-gray-500 font-mono">
                Qiita投稿: {{ $payload['posted_at'] }}
            </time>
        @endif
        
        <a href="{{ $payload['url'] ?? '#' }}" target="_blank" 
           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-semibold hover:bg-indigo-200 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            開く
        </a>
    </div>

</div>
