{{--
  円形プログレス表示コンポーネント (SVGベース)
  
  Parameters:
  - $current: 現在の進捗数
  - $required: 必要数
  - $colorClass: 色クラス (例: 'text-indigo-600')
  - $size: サイズ (デフォルト: 64)
--}}
@php
    $size = $size ?? 64;
    $strokeWidth = 4;
    $radius = ($size / 2) - ($strokeWidth * 2);
    $circumference = 2 * pi() * $radius;
    $percentage = $required > 0 ? min(100, ($current / $required) * 100) : 0;
    $offset = $circumference - ($percentage / 100) * $circumference;
@endphp

<div class="relative inline-flex items-center justify-center" style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg class="transform -rotate-90" width="{{ $size }}" height="{{ $size }}">
        {{-- 背景円 --}}
        <circle
            cx="{{ $size / 2 }}"
            cy="{{ $size / 2 }}"
            r="{{ $radius }}"
            stroke="currentColor"
            stroke-width="{{ $strokeWidth }}"
            fill="none"
            class="text-gray-200"
        />
        {{-- 進捗円 --}}
        <circle
            cx="{{ $size / 2 }}"
            cy="{{ $size / 2 }}"
            r="{{ $radius }}"
            stroke="currentColor"
            stroke-width="{{ $strokeWidth }}"
            fill="none"
            stroke-dasharray="{{ $circumference }}"
            stroke-dashoffset="{{ $offset }}"
            stroke-linecap="round"
            class="{{ $colorClass }} transition-all duration-500"
        />
    </svg>
    {{-- 中央テキスト --}}
    <div class="absolute inset-0 flex items-center justify-center">
        <span class="text-xs font-bold text-gray-700">{{ $current }}/{{ $required }}</span>
    </div>
</div>
