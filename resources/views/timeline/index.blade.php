@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    
    // 時間軸でグループ化
    $groupedEvents = $events->groupBy(function($event) {
        $date = $event->occurred_at;
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
    
    .event-card {
        animation: fadeInUp 0.5s ease-out;
        animation-fill-mode: both;
    }
    
    .event-card:hover {
        transform: translateY(-4px);
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
                タイムライン
            </h1>
            <p class="text-sm text-gray-500 mt-2">
                メンバーの活動を時系列で確認
            </p>
        </div>
        <div class="text-4xl">🚀</div>
    </div>

    {{-- タイプ切り替えタブ --}}
    @php
        $currentType = $type ?? 'all';
    @endphp
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('timeline.index', ['type' => 'all']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 
                  {{ ($currentType === 'all' || $currentType === '') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
            <span>🌟</span>
            すべて
        </a>
        <a href="{{ route('timeline.index', ['type' => 'qiita']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 
                  {{ $currentType === 'qiita' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
            <span>📝</span>
            技術ブログ
        </a>
        <a href="{{ route('timeline.index', ['type' => 'event_hosting']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 
                  {{ $currentType === 'event_hosting' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
            <span>🎉</span>
            イベント企画
        </a>
        <a href="{{ route('timeline.index', ['type' => 'event_speaking']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 
                  {{ $currentType === 'event_speaking' ? 'bg-gradient-to-r from-pink-600 to-rose-600 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
            <span>🎤</span>
            イベント登壇
        </a>
        <a href="{{ route('timeline.index', ['type' => 'certification']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 
                  {{ $currentType === 'certification' ? 'bg-gradient-to-r from-amber-600 to-orange-600 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
            <span>🏆</span>
            資格取得
        </a>
    </div>

    {{-- タイムライン --}}
    @forelse ($groupedEvents as $timeGroup => $eventsInGroup)
        {{-- 時間グループヘッダー --}}
        <div class="sticky top-0 z-10 -mx-4 px-4 py-3 backdrop-blur-md bg-white/70 border-b border-gray-200">
            <h2 class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ $timeGroup }}
            </h2>
        </div>

        {{-- イベントカードグリッド --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($eventsInGroup as $index => $event)
                @php
                    // イベントタイプごとの設定
                    $typeConfig = match($event->event_type) {
                        'qiita' => ['icon' => '📝', 'label' => '技術ブログ', 'color' => 'indigo'],
                        'event_hosting' => ['icon' => '🎉', 'label' => 'イベント企画', 'color' => 'purple'],
                        'event_speaking' => ['icon' => '🎤', 'label' => 'イベント登壇', 'color' => 'pink'],
                        'certification' => ['icon' => '🏆', 'label' => '資格取得', 'color' => 'amber'],
                    };
                    
                    // ユーザーごとに色を生成
                    $userHash = crc32($event->user->name);
                    $hue = $userHash % 360;
                @endphp

                <div class="event-card group relative rounded-2xl border border-gray-200 backdrop-blur-sm shadow-md hover:shadow-2xl transition-all duration-300"
                     style="animation-delay: {{ $index * 0.05 }}s">
                    
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
                                    {{ mb_substr($event->user->name, 0, 2) }}
                                </div>
                            </div>
                            
                            {{-- メタ情報 --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $event->user->name }}</p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                    <span class="inline-flex items-center gap-1">
                                        <span>{{ $typeConfig['icon'] }}</span>
                                        {{ $typeConfig['label'] }}
                                    </span>
                                    <span>·</span>
                                    <time class="font-mono">{{ $event->occurred_at->diffForHumans() }}</time>
                                </div>
                            </div>
                        </div>

                        {{-- イベント内容（タイプ別） --}}
                        @if ($event->event_type === 'qiita')
                            @include('timeline.partials.qiita', ['event' => $event])
                        @else
                            @include('timeline.partials.form-based', ['event' => $event])
                        @endif

                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="text-center py-20">
            <div class="text-6xl mb-4">📋</div>
            <p class="text-lg text-gray-500">まだ活動は記録されていません</p>
        </div>
    @endforelse

    {{-- ページネーション --}}
    <div class="mt-8">
        {{ $events->appends(['type' => $type])->links() }}
    </div>

</div>
@endsection
