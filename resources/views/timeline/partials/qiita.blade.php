@php
    use Illuminate\Support\Str;
    $hasSummary = !empty($article->summary);
    $displayTags = array_slice($article->tags ?? [], 0, 3);
    $remainingTagsCount = count($article->tags ?? []) - count($displayTags);
    $summaryPreview = $hasSummary ? Str::limit($article->summary, 80) : '';
    
    // ユーザーごとに色を生成
    $userHash = crc32($article->user->name);
    $hue = $userHash % 360;
@endphp

<div class="group relative rounded-lg border border-gray-200 backdrop-blur-sm shadow-sm hover:shadow-lg transition-all duration-300 {{ $hasSummary ? 'bg-gradient-to-br from-indigo-50/50 to-purple-50/50' : 'bg-gradient-to-br from-amber-50/50 to-orange-50/50' }}"
     x-data="{ 
         expanded: false,
         liked: false,
         likeCount: {{ $article->likes_count }}
     }">
    
    {{-- グラスモーフィズム効果 --}}
    <div class="absolute inset-0 bg-white/60 backdrop-blur-xl rounded-lg"></div>
    
    {{-- カード内容 --}}
    <div class="relative p-3 space-y-2">
        
        {{-- ヘッダー：アバター + メタ情報 --}}
        <div class="flex items-start gap-2">
            {{-- アバター --}}
            <div class="shrink-0 relative">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shadow"
                     style="background: linear-gradient(135deg, hsl({{ $hue }}, 70%, 50%) 0%, hsl({{ ($hue + 60) % 360 }}, 70%, 60%) 100%)">
                    {{ mb_substr($article->user->name, 0, 2) }}
                </div>
                @if($hasSummary)
                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-indigo-500 rounded-full flex items-center justify-center text-white text-[8px]">
                    ✓
                </div>
                @endif
            </div>
            
            {{-- メタ情報 --}}
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm text-gray-900 truncate">{{ $article->user->name }}</p>
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span>📝 技術ブログ</span>
                    <span>·</span>
                    <time class="font-mono text-xs">{{ $article->created_at->diffForHumans() }}</time>
                </div>
            </div>
            
            {{-- クイックアクション（ホバー時表示） --}}
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <a href="{{ $article->url }}" target="_blank" 
                   class="inline-flex items-center gap-0.5 px-2 py-1 rounded-md bg-indigo-100 text-indigo-700 text-xs font-semibold hover:bg-indigo-200 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    開く
                </a>
            </div>
        </div>

        {{-- タイトル --}}
        <div>
            <a href="{{ $article->url }}" target="_blank"
               class="block text-base font-bold text-gray-900 hover:text-indigo-600 transition-colors leading-snug line-clamp-2">
                {{ $article->title }}
            </a>
        </div>

        {{-- タグ --}}
        @if (!empty($displayTags))
            <div class="flex flex-wrap gap-1 items-center">
                @foreach ($displayTags as $tag)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-700 text-xs font-medium border border-indigo-100">
                        #{{ $tag }}
                    </span>
                @endforeach
                @if ($remainingTagsCount > 0)
                    <span class="text-xs text-gray-400 font-medium">+{{ $remainingTagsCount }}</span>
                @endif
            </div>
        @endif

        {{-- AI要約（コンパクト化） --}}
        @if ($hasSummary)
            <div class="text-xs">
                <div x-show="!expanded" class="flex items-start gap-1.5">
                    <div class="flex-1">
                        <span class="inline-flex items-center gap-0.5 text-indigo-600 font-semibold text-xs mb-0.5">
                            💡 AI要約
                        </span>
                        <p class="text-gray-700 text-xs">{{ $summaryPreview }}</p>
                    </div>
                    <button @click="expanded = true" 
                            class="shrink-0 text-indigo-600 hover:text-indigo-800 font-semibold text-xs px-1.5 py-0.5 rounded hover:bg-indigo-50 transition">
                        もっと見る
                    </button>
                </div>

                <div x-show="expanded" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="rounded-lg bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 p-2.5">
                        <div class="flex items-center justify-between mb-1">
                            <span class="inline-flex items-center gap-0.5 text-indigo-700 font-bold text-xs">
                                💡 AI要約
                            </span>
                            <button @click="expanded = false" 
                                    class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold">
                                閉じる
                            </button>
                        </div>
                        <p class="whitespace-pre-wrap text-gray-800 leading-relaxed text-xs">{{ $article->summary }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- フッター：いいね + 投稿日 --}}
        <div class="flex items-center justify-between pt-1.5 border-t border-gray-100">
            <button @click="liked = !liked; likeCount += liked ? 1 : -1" 
                    class="inline-flex items-center gap-1 px-2 py-1 rounded-md transition-all duration-200 text-xs"
                    :class="liked ? 'bg-pink-100 text-pink-600' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                <svg class="w-3.5 h-3.5" :class="liked && 'fill-current'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span class="font-bold" x-text="likeCount"></span>
            </button>
            
            @if ($article->posted_at)
                <time class="text-xs text-gray-500 font-mono">
                    {{ $article->posted_at->format('Y/m/d') }}
                </time>
            @endif
        </div>

    </div>
</div>

