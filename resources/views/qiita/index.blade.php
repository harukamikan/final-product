@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    
    // 時間軸でグループ化
    $groupedArticles = $articles->groupBy(function($article) {
        $date = $article->created_at;
        $now = Carbon::now();
        
        if ($date->isToday()) {
            return '今日';
        } elseif ($date->isYesterday()) {
            return '昨日';
        } elseif ($date->isCurrentWeek()) {
            return '今週';
        } elseif ($date->isLastWeek()) {
            return '先週';
        } else {
            return $date->format('Y年m月');
        }
    });
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    
    .timeline-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse-ring {
        0% {
            box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
        }
    }
    
    @keyframes heartbeat {
        0%, 100% {
            transform: scale(1);
        }
        10%, 30% {
            transform: scale(0.9);
        }
        20%, 40% {
            transform: scale(1.1);
        }
    }
    
    .article-card {
        animation: fadeInUp 0.5s ease-out;
        animation-fill-mode: both;
    }
    
    .article-card:hover {
        transform: translateY(-4px);
    }
    
    .has-summary {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.03) 0%, rgba(139, 92, 246, 0.03) 100%);
    }
    
    .no-summary {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.03) 0%, rgba(249, 115, 22, 0.03) 100%);
    }
    
    .like-button:active {
        animation: heartbeat 0.6s ease;
    }
    
    .avatar-ring {
        animation: pulse-ring 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
    }
    
    [x-cloak] {
        display: none !important;
    }
</style>

<div class="timeline-container max-w-7xl mx-auto px-4 py-8 space-y-8">

    {{-- ヘッダー --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                みんなのQiitaタイムライン
            </h1>
            <p class="text-sm text-gray-500 mt-2">
                メンバーがミッションとして登録したQiita記事の一覧です
            </p>
        </div>
        <div class="text-4xl">✨</div>
    </div>

    {{-- タイムライン --}}
    @forelse ($groupedArticles as $timeGroup => $articlesInGroup)
        {{-- 時間グループヘッダー --}}
        <div class="sticky top-0 z-10 -mx-4 px-4 py-3 backdrop-blur-md bg-white/70 border-b border-gray-200">
            <h2 class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ $timeGroup }}
            </h2>
        </div>

        {{-- 記事カードグリッド --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($articlesInGroup as $index => $article)
                @php
                    $hasSummary = !empty($article->summary);
                    $displayTags = array_slice($article->tags ?? [], 0, 3);
                    $remainingTagsCount = count($article->tags ?? []) - count($displayTags);
                    $summaryPreview = $hasSummary ? Str::limit($article->summary, 80) : '';
                    
                    // ユーザーごとに色を生成
                    $userHash = crc32($article->user->name);
                    $hue = $userHash % 360;
                @endphp

                <div class="article-card group relative rounded-2xl border border-gray-200 backdrop-blur-sm shadow-md hover:shadow-2xl transition-all duration-300 {{ $hasSummary ? 'has-summary' : 'no-summary' }}"
                     style="animation-delay: {{ $index * 0.05 }}s"
                     x-data="{ 
                         expanded: false,
                         liked: false,
                         likeCount: {{ $article->likes_count }}
                     }">
                    
                    {{-- グラスモーフィズム効果 --}}
                    <div class="absolute inset-0 bg-white/60 backdrop-blur-xl rounded-2xl"></div>
                    
                    {{-- カード内容 --}}
                    <div class="relative p-5 space-y-3">
                        
                        {{-- ヘッダー：アバター + メタ情報 --}}
                        <div class="flex items-start gap-3">
                            {{-- アバター --}}
                            <div class="shrink-0 relative">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-sm font-bold shadow-lg"
                                     style="background: linear-gradient(135deg, hsl({{ $hue }}, 70%, 50%) 0%, hsl({{ ($hue + 60) % 360 }}, 70%, 60%) 100%)">
                                    {{ mb_substr($article->user->name, 0, 2) }}
                                </div>
                                @if($hasSummary)
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xs">
                                    ✓
                                </div>
                                @endif
                            </div>
                            
                            {{-- メタ情報 --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $article->user->name }}</p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        技術ブログ
                                    </span>
                                    <span>·</span>
                                    <time class="font-mono">{{ $article->created_at->diffForHumans() }}</time>
                                </div>
                            </div>
                            
                            {{-- クイックアクション（ホバー時表示） --}}
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <a href="{{ $article->url }}" target="_blank" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-semibold hover:bg-indigo-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    開く
                                </a>
                            </div>
                        </div>

                        {{-- タイトル --}}
                        <div>
                            <a href="{{ $article->url }}" target="_blank"
                               class="block text-xl font-bold text-gray-900 hover:text-indigo-600 transition-colors leading-tight line-clamp-2">
                                {{ $article->title }}
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
                                        <p class="whitespace-pre-wrap text-gray-800 leading-relaxed">{{ $article->summary }}</p>
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
                                    class="like-button inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all duration-200"
                                    :class="liked ? 'bg-pink-100 text-pink-600' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                <svg class="w-4 h-4" :class="liked && 'fill-current'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span class="font-bold text-sm" x-text="likeCount"></span>
                            </button>
                            
                            @if ($article->posted_at)
                                <time class="text-xs text-gray-500 font-mono">
                                    Qiita投稿: {{ $article->posted_at->format('Y/m/d') }}
                                </time>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="text-center py-20">
            <div class="text-6xl mb-4">📝</div>
            <p class="text-lg text-gray-500">まだQiita記事は登録されていません</p>
        </div>
    @endforelse

    {{-- ページネーション --}}
    <div class="mt-8">
        {{ $articles->links() }}
    </div>

</div>
@endsection
