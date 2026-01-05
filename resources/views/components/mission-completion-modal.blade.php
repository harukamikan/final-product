{{--
    ミッション完了モーダル
    
    使用方法:
    @include('components.mission-completion-modal', [
        'achievementData' => [
            'earned_miles' => 150,
            'mission_title' => 'ミッション名',
            'rank_info' => [...],
            'next_action' => [...],
        ]
    ])
--}}

@if(isset($achievementData) && $achievementData['mission_completed'] ?? false)
<div 
    x-data="{ 
        open: true,
        earnedMiles: {{ $achievementData['earned_miles'] ?? 0 }},
        displayedMiles: 0,
        isPulsing: false,
        init() {
            // マイルのカウントアップアニメーション
            this.$nextTick(() => {
                const duration = 600;
                const steps = 30;
                const increment = this.earnedMiles / steps;
                const stepDuration = duration / steps;
                
                // アニメーション開始時にパルス効果を開始
                setTimeout(() => { this.isPulsing = true; }, 100);
                
                let currentStep = 0;
                const interval = setInterval(() => {
                    currentStep++;
                    if (currentStep >= steps) {
                        this.displayedMiles = this.earnedMiles;
                        clearInterval(interval);
                        // アニメーション終了後にパルス効果を停止
                        setTimeout(() => { this.isPulsing = false; }, 200);
                    } else {
                        this.displayedMiles = Math.floor(increment * currentStep);
                    }
                }, stepDuration);
            });
        }
    }"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
    @click.self="open = false"
    style="display: none;"
>
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300 delay-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden"
        @click.stop
    >
        {{-- ヘッダー部分 --}}
        <div class="bg-gradient-to-r {{ \App\Helpers\RankHelper::getRankColor($achievementData['rank_info']['current_rank'] ?? 'ブロンズ') }} text-white px-6 py-8 text-center">
            <div class="text-6xl mb-3">🎉</div>
            <h2 class="text-2xl font-bold mb-2">ミッション達成！</h2>
            <p class="text-sm opacity-90">{{ $achievementData['mission_title'] ?? '' }}</p>
        </div>

        {{-- コンテンツ部分 --}}
        <div class="px-6 py-6 space-y-6">
            {{-- 獲得マイル --}}
            <div class="text-center relative py-4">
                {{-- パーティクルエフェクト --}}
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div 
                        x-show="isPulsing"
                        x-transition:enter="transition-all duration-500"
                        x-transition:enter-start="opacity-0 scale-50"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="absolute inset-0 flex items-center justify-center"
                    >
                        {{-- グロー効果 --}}
                        <div class="absolute w-32 h-32 bg-indigo-400 rounded-full opacity-20 blur-3xl animate-pulse"></div>
                        
                        {{-- スパークル --}}
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48">
                            @for($i = 0; $i < 8; $i++)
                                <div 
                                    class="absolute top-1/2 left-1/2 w-1 h-1 bg-yellow-400 rounded-full"
                                    style="
                                        transform: rotate({{ $i * 45 }}deg) translateY(-60px);
                                        animation: sparkle {{ 0.6 + ($i * 0.05) }}s ease-out forwards;
                                        opacity: 0;
                                    "
                                ></div>
                            @endfor
                        </div>
                    </div>
                </div>
                
                <p class="text-sm text-gray-500 mb-2">獲得マイル</p>
                <div 
                    class="relative inline-block"
                    :class="{ 'animate-bounce-subtle': isPulsing }"
                >
                    <p class="text-6xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 relative z-10">
                        <span x-text="'+' + displayedMiles"></span>
                    </p>
                    <p class="text-2xl opacity-70 ml-2 text-indigo-400 inline-block">miles</p>
                </div>
                </p>
            </div>

            @if($achievementData['rank_info']['rank_up'] ?? false)
                {{-- ランクアップメッセージ --}}
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-300 rounded-2xl p-4 text-center">
                    <div class="text-4xl mb-2">👑</div>
                    <p class="font-bold text-lg text-yellow-800">ランクアップ！</p>
                    <p class="text-sm text-yellow-700 mt-1">
                        {{ $achievementData['rank_info']['previous_rank'] }} → 
                        <span class="font-bold">{{ $achievementData['rank_info']['current_rank'] }}</span>
                    </p>
                </div>
            @endif

            {{-- ランク進捗 --}}
            <div class="bg-gray-50 rounded-2xl p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium {{ \App\Helpers\RankHelper::getRankTextColor($achievementData['rank_info']['current_rank'] ?? 'ブロンズ') }}">
                        現在のランク: {{ $achievementData['rank_info']['current_rank'] ?? 'ブロンズ' }}
                    </span>
                    @if($achievementData['rank_info']['next_rank'] ?? null)
                        <span class="text-xs text-gray-500">
                            次のランクまであと {{ $achievementData['rank_info']['miles_to_next'] ?? 0 }} miles
                        </span>
                    @else
                        <span class="text-xs text-gray-500">
                            最高ランク！
                        </span>
                    @endif
                </div>
                
                @if($achievementData['rank_info']['next_rank'] ?? null)
                    <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r {{ \App\Helpers\RankHelper::getRankColor($achievementData['rank_info']['current_rank'] ?? 'ブロンズ') }} rounded-full transition-all duration-500 ease-out"
                            style="width: {{ \App\Helpers\RankHelper::calculateProgress($achievementData['rank_info']['current_miles'] ?? 0, $achievementData['rank_info']['current_rank'] ?? 'ブロンズ') }}%;"
                        ></div>
                    </div>
                @endif
            </div>

            {{-- 次のアクション --}}
            @if($achievementData['next_action'] ?? null)
                <div class="border-t pt-4">
                    <p class="text-sm text-gray-500 mb-3 text-center">次のアクション</p>
                    <a 
                        href="{{ $achievementData['next_action']['url'] ?? '#' }}"
                        class="block w-full text-center px-4 py-3 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition"
                    >
                        {{ $achievementData['next_action']['title'] ?? '次のミッションへ' }}
                    </a>
                </div>
            @endif
        </div>

        {{-- 閉じるボタン --}}
        <button 
            @click="open = false"
            class="absolute top-4 right-4 text-white/80 hover:text-white transition p-2"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        {{-- 下部のボタン --}}
        <div class="px-6 pb-6">
            <button 
                @click="
                    open = false;
                    // マイル表示更新イベントを発火
                    $dispatch('miles-updated', {
                        previousMiles: {{ ($achievementData['rank_info']['current_miles'] ?? 0) - ($achievementData['earned_miles'] ?? 0) }},
                        newMiles: {{ $achievementData['rank_info']['current_miles'] ?? 0 }},
                        earnedMiles: {{ $achievementData['earned_miles'] ?? 0 }}
                    });
                "
                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 text-gray-600 font-medium hover:bg-gray-50 transition"
            >
                閉じる
            </button>
        </div>
    </div>
</div>
@endif
