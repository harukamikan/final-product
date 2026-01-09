@php
    $payload = $cert->payload;
    
    // ユーザーごとに色を生成
    $userHash = crc32($cert->user->name);
    $hue = $userHash % 360;
    
    // 難易度に応じた色
    $difficultyColors = [
        '初級' => 'bg-green-100 text-green-700 border-green-200',
        '中級' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        '上級' => 'bg-red-100 text-red-700 border-red-200',
    ];
    $difficultyClass = $difficultyColors[$payload['difficulty'] ?? ''] ?? 'bg-gray-100 text-gray-700 border-gray-200';
@endphp

<div class="group relative rounded-lg border border-gray-200 backdrop-blur-sm shadow-sm hover:shadow-lg transition-all duration-300 bg-gradient-to-br from-blue-50/50 to-cyan-50/50">
    
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
                    {{ mb_substr($cert->user->name, 0, 2) }}
                </div>
                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-blue-500 rounded-full flex items-center justify-center text-white text-[8px]">
                    🏆
                </div>
            </div>
            
            {{-- メタ情報 --}}
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm text-gray-900 truncate">{{ $cert->user->name }}</p>
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span>🎓 資格取得</span>
                    <span>·</span>
                    <time class="font-mono text-xs">{{ $cert->created_at->diffForHumans() }}</time>
                </div>
            </div>
            
            {{-- 難易度バッジ --}}
            @if(!empty($payload['difficulty']))
            <div class="shrink-0">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $difficultyClass }}">
                    {{ $payload['difficulty'] }}
                </span>
            </div>
            @endif
        </div>

        {{-- 資格名 --}}
        <div>
            <h3 class="text-base font-bold text-gray-900 leading-snug">
                {{ $payload['name'] }}
            </h3>
        </div>

        {{-- 主催団体 --}}
        @if(!empty($payload['organization']))
            <div class="flex items-center gap-1 text-xs">
                <span class="text-gray-500">🏢</span>
                <span class="text-gray-700 font-medium">{{ $payload['organization'] }}</span>
            </div>
        @endif

        {{-- スコア --}}
        @if(!empty($payload['score']))
            <div class="flex items-center gap-1 text-xs">
                <span class="text-gray-500">📊</span>
                <span class="text-gray-700 font-medium">{{ $payload['score'] }}</span>
            </div>
        @endif

        {{-- メモ --}}
        @if(!empty($payload['memo']))
            <div class="rounded-lg bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-200 p-2">
                <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-wrap line-clamp-2">{{ $payload['memo'] }}</p>
            </div>
        @endif

        {{-- フッター：取得日 --}}
        <div class="flex items-center justify-between pt-1.5 border-t border-gray-100">
            <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                </svg>
                {{ \Carbon\Carbon::parse($payload['acquired_at'])->format('Y/m/d') }}
            </span>
        </div>

    </div>
</div>

