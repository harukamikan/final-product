@extends('layouts.app')

@section('content')
@php
    $via = $via ?? 'gacha';
@endphp

<div class="min-h-screen flex items-center justify-center">
    <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-lg p-8">

        {{-- ×ボタン --}}
        <a
            href="{{ route('rewards.gacha') }}"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">
            ×
        </a>

        {{-- タイトル --}}
        <h2 class="text-xl font-bold text-center mb-6">
            🎉 {{ $via === 'scratch' ? 'スクラッチ結果' : 'ガチャ結果' }} 🎉
        </h2>

        @if($via === 'scratch')

        <div class="flex flex-col items-center space-y-6">

            <p class="text-sm text-gray-500">
                削って報酬を確認してください
            </p>

            {{-- ===== スクラッチ枠（重要） ===== --}}
            <div
                id="scratchWrapper"
                class="relative w-80 h-48 overflow-hidden rounded-xl">

                {{-- 下層（報酬） --}}
                <div
                    id="rewardCard"
                    class="absolute inset-0 flex items-center justify-center
                           bg-yellow-100 text-2xl font-semibold text-indigo-600
                           scale-95 opacity-0 transition-all duration-500">
                    {{ $reward->name }}
                </div>

                {{-- 上層（削る部分） --}}
                <canvas
                    id="scratchCanvas"
                    class="absolute inset-0">
                </canvas>
            </div>

            {{-- 戻るボタン --}}
            <a
                href="{{ route('rewards.gacha') }}"
                id="backBtn"
                class="hidden px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                戻る
            </a>

        </div>

        @else

        {{-- 通常ガチャ --}}
        <div class="border-2 border-dashed border-indigo-400 rounded-xl p-6 text-center">
            <p class="text-sm text-gray-500 mb-2">獲得した報酬</p>
            <p class="text-2xl font-semibold text-indigo-600">
                {{ $reward->name }}
            </p>
        </div>

        @endif
    </div>
</div>

{{-- ===== スクラッチJS ===== --}}
@if($via === 'scratch')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('scratchCanvas');
    const wrapper = document.getElementById('scratchWrapper');
    const rewardCard = document.getElementById('rewardCard');
    const backBtn = document.getElementById('backBtn');

    const ctx = canvas.getContext('2d');

    // サイズ完全一致（黄色はみ出し防止）
    canvas.width = wrapper.offsetWidth;
    canvas.height = wrapper.offsetHeight;

    // 初期：銀色で完全に覆う
    ctx.globalCompositeOperation = 'source-over';
    ctx.fillStyle = '#bfbfbf';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.globalCompositeOperation = 'destination-out';

    let isDrawing = false;
    let revealed = false;

    const start = () => isDrawing = true;
    const end = () => {
        isDrawing = false;
        checkCleared();
    };

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mousemove', draw);

    canvas.addEventListener('touchstart', start);
    canvas.addEventListener('touchend', end);
    canvas.addEventListener('touchmove', draw);

    function draw(e) {
        if (!isDrawing || revealed) return;

        const rect = canvas.getBoundingClientRect();
        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
        const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;

        ctx.beginPath();
        ctx.arc(x, y, 18, 0, Math.PI * 2);
        ctx.fill();
    }

    function checkCleared() {
        if (revealed) return;

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        let cleared = 0;

        for (let i = 3; i < imageData.data.length; i += 4) {
            if (imageData.data[i] === 0) cleared++;
        }

        const percent = cleared / (canvas.width * canvas.height) * 100;

        if (percent > 40) {
            revealReward();
        }
    }

    function revealReward() {
        revealed = true;

        // Canvas フェードアウト
        canvas.style.transition = 'opacity 0.6s ease';
        canvas.style.opacity = 0;

        // 報酬表示
        rewardCard.classList.remove('opacity-0', 'scale-95');
        rewardCard.classList.add('opacity-100', 'scale-100');

        // 戻るボタン表示
        backBtn.classList.remove('hidden');
    }
});
</script>
@endif
@endsection
