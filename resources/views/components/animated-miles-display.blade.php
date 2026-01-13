{{--
    アニメーション付きマイル表示コンポーネント
    
    使用方法:
    @include('components.animated-miles-display', [
        'currentMiles' => $totalMiles,
        'size' => 'large', // 'small', 'medium', 'large'
        'showIcon' => true,
        'icon' => '🎯',
        'label' => '現在のマイル残高'
    ])
--}}

@php
    $sizeClasses = [
        'small' => 'text-2xl',
        'medium' => 'text-4xl',
        'large' => 'text-5xl',
    ];
    $textSize = $sizeClasses[$size ?? 'medium'] ?? $sizeClasses['medium'];
@endphp

<div 
    class="relative"
    x-data="animatedMiles"
    x-init="init({{ $currentMiles ?? 0 }})"
    @miles-updated.window="onMilesUpdated($event.detail)"
>
    {{-- アイコンを右上角に配置 --}}
    @if($showIcon ?? true)
        <div class="absolute top-0 right-0 text-5xl opacity-80" :class="{ 'animate-bounce': isPulsing }">
            {{ $icon ?? '🎯' }}
        </div>
    @endif
    
    <div>
        <p class="text-sm font-medium opacity-90">{{ $label ?? '現在のマイル残高' }}</p>
        <div 
            class="relative mt-2"
            :class="{ 'animate-bounce-subtle': isPulsing }"
        >
            {{-- グローエフェクト --}}
            <div 
                x-show="isPulsing"
                x-transition
                class="absolute inset-0 bg-white/30 rounded-full blur-xl"
            ></div>
            
            <p class="{{ $textSize }} font-bold relative z-10">
                <span x-text="displayedMiles.toLocaleString()"></span>
                <span class="text-xl opacity-70 ml-1">mile</span>
            </p>
        </div>
        <p class="text-xs opacity-75 mt-1">ミッション達成で獲得</p>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('animatedMiles', () => ({
        displayedMiles: 0,
        targetMiles: 0,
        isPulsing: false,
        
        init(initialMiles) {
            this.displayedMiles = initialMiles;
            this.targetMiles = initialMiles;
        },
        
        onMilesUpdated(data) {
            const { previousMiles, newMiles, earnedMiles } = data;
            
            // すでに新しい値の場合は何もしない
            if (this.displayedMiles === newMiles) {
                return;
            }
            
            this.targetMiles = newMiles;
            this.isPulsing = true;
            
            // カウントアップアニメーション
            const duration = 800;
            const steps = 40;
            const increment = (newMiles - this.displayedMiles) / steps;
            const stepDuration = duration / steps;
            
            let currentStep = 0;
            const interval = setInterval(() => {
                currentStep++;
                if (currentStep >= steps) {
                    this.displayedMiles = newMiles;
                    clearInterval(interval);
                    // パルス効果を停止
                    setTimeout(() => { this.isPulsing = false; }, 300);
                } else {
                    this.displayedMiles = Math.floor(this.displayedMiles + increment);
                }
            }, stepDuration);
        }
    }));
});
</script>
