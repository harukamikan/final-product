@php
    $payload = $event->payload;
    $apiFetched = $payload['api_fetched'] ?? false;
    $typeIcon = $eventType === 'hosting' ? '🎉' : '🎤';
    $typeLabel = $eventType === 'hosting' ? 'イベント企画・開催' : 'イベント登壇';
    
    // ユーザーごとに色を生成
    $userHash = crc32($event->user->name);
    $hue = $userHash % 360;
@endphp

<div class="group relative rounded-lg border border-gray-200 backdrop-blur-sm shadow-sm hover:shadow-lg transition-all duration-300 bg-gradient-to-br from-green-50/50 to-emerald-50/50">
    
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
                    {{ mb_substr($event->user->name, 0, 2) }}
                </div>
                @if($apiFetched)
                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full flex items-center justify-center text-white text-[8px]">
                    ✓
                </div>
                @endif
            </div>
            
            {{-- メタ情報 --}}
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm text-gray-900 truncate">{{ $event->user->name }}</p>
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span>{{ $typeIcon }} {{ $typeLabel }}</span>
                    <span>·</span>
                    <time class="font-mono text-xs">{{ $event->created_at->diffForHumans() }}</time>
                </div>
            </div>
            
            {{-- クイックアクション（ホバー時表示） --}}
            @if(isset($payload['url']))
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <a href="{{ $payload['url'] }}" target="_blank" 
                   class="inline-flex items-center gap-0.5 px-2 py-1 rounded-md bg-green-100 text-green-700 text-xs font-semibold hover:bg-green-200 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    開く
                </a>
            </div>
            @endif
        </div>

        {{-- タイトル --}}
        <div>
            @if(isset($payload['url']))
                <a href="{{ $payload['url'] }}" target="_blank"
                   class="block text-base font-bold text-gray-900 hover:text-green-600 transition-colors leading-snug line-clamp-2">
                    {{ $payload['title'] ?? 'イベント' }}
                </a>
            @else
                <h3 class="text-base font-bold text-gray-900 leading-snug line-clamp-2">
                    {{ $payload['title'] ?? 'イベント' }}
                </h3>
            @endif
        </div>

        {{-- キャッチコピー（APIから取得できた場合） --}}
        @if($apiFetched && !empty($payload['catch']))
            <div class="text-xs text-gray-700 line-clamp-2">
                {{ $payload['catch'] }}
            </div>
        @endif

        {{-- イベント詳細情報 --}}
        <div class="grid grid-cols-2 gap-1.5 text-xs">
            {{-- 開催日時 --}}
            @if(isset($payload['started_at']))
                <div class="flex items-center gap-1">
                    <span class="text-gray-500">📅</span>
                    <span class="text-gray-700 font-medium">{{ \Carbon\Carbon::parse($payload['started_at'])->format('Y/m/d H:i') }}</span>
                </div>
            @endif

            {{-- 開催場所 --}}
            @if(!empty($payload['place']))
                <div class="flex items-center gap-1">
                    <span class="text-gray-500">📍</span>
                    <span class="text-gray-700 truncate">{{ $payload['place'] }}</span>
                </div>
            @endif

            {{-- 参加者数 --}}
            @if(isset($payload['accepted']) && isset($payload['limit']))
                <div class="flex items-center gap-1">
                    <span class="text-gray-500">👥</span>
                    <span class="text-gray-700 font-medium">{{ $payload['accepted'] }}/{{ $payload['limit'] }}人</span>
                </div>
            @endif
        </div>

        {{-- API取得失敗の警告 --}}
        @if(!$apiFetched)
            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold border border-amber-200">
                ⚠️ イベント情報取得失敗
            </div>
        @endif

        {{-- フッター：connpassリンク --}}
        <div class="flex items-center justify-between pt-1.5 border-t border-gray-100">
            @if(isset($payload['url']) && str_contains($payload['url'], 'connpass'))
                <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    connpass
                </span>
            @endif
            
            @if(isset($payload['started_at']))
                <time class="text-xs text-gray-500 font-mono">
                    {{ \Carbon\Carbon::parse($payload['started_at'])->format('Y/m/d') }}
                </time>
            @endif
        </div>

    </div>
</div>

