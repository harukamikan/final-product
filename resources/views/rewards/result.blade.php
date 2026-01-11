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
                @if($via === 'scratch')
                <div
                    id="rewardCard"
                    class="absolute inset-0 flex items-center justify-center
                           bg-yellow-100 text-2xl font-bold text-indigo-600
                           scale-95 opacity-0 transition-all duration-500">
                    @if($miles > 0)
                    🎉 {{ $miles }} マイル獲得！
                    @else
                    😢 はずれ…
                    @endif
                </div>
                @endif


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

        {{-- ガチャ演出 --}}
        <div class="flex flex-col items-center space-y-6">

            <p id="gachaText" class="text-sm text-gray-500">
                ガチャを回しています…
            </p>

            {{--ガチャカード--}}
            <div
                id="gachaCard"
                data-reward-name="{{ $reward->name }}"
                class="w-64 h-32 flex items-center justify-center
           bg-indigo-100 rounded-xl
           text-2xl font-bold text-indigo-600
           transition-all duration-300">
                ？
            </div>


            {{--戻るボタン--}}
            <a
                href="{{ route('rewards.gacha') }}"
                id="gachaBackBtn"
                class="hidden px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                戻る
            </a>
        </div>

        @endif
    </div>
</div>

{{--スクラッチJS--}}
@if($via === 'scratch')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('scratchCanvas');
        const wrapper = document.getElementById('scratchWrapper');
        const rewardCard = document.getElementById('rewardCard');
        const backBtn = document.getElementById('backBtn');

        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        canvas.width = wrapper.offsetWidth;
        canvas.height = wrapper.offsetHeight;

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
            const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
            let cleared = 0;
            for (let i = 3; i < data.length; i += 4) {
                if (data[i] === 0) cleared++;
            }
            if ((cleared / (canvas.width * canvas.height)) * 100 > 40) {
                revealReward();
            }
        }

        function revealReward() {
            revealed = true;
            canvas.style.transition = 'opacity 0.6s ease';
            canvas.style.opacity = 0;
            rewardCard.classList.remove('opacity-0', 'scale-95');
            rewardCard.classList.add('opacity-100', 'scale-100');
            backBtn.classList.remove('hidden');
        }
    });
</script>
@endif

{{--ガチャJS--}}
@if($via !== 'scratch')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const card = document.getElementById('gachaCard');
        const backBtn = document.getElementById('gachaBackBtn');
        const text = document.getElementById('gachaText');

        if (!card || !backBtn || !text) return;

        const dummyRewards = ['？', '🎁', '⭐', '💎', '📦', '🎫'];
        let count = 0;
        let speed = 80;
        const maxCount = 25;

        const timer = setInterval(() => {
            card.textContent =
                dummyRewards[Math.floor(Math.random() * dummyRewards.length)];
            count++;
            speed += 20;
            if (count > maxCount) {
                clearInterval(timer);
                revealReward();
            }
        }, speed);

        function revealReward() {
            const rewardName = card.dataset.rewardName;
            if (!rewardName) return;

            card.textContent = rewardName;
            card.classList.add('scale-110');
            setTimeout(() => card.classList.remove('scale-110'), 300);
            card.classList.replace('bg-indigo-100', 'bg-yellow-100');
            text.textContent = '獲得しました！';
            backBtn.classList.remove('hidden');
        }
    });
</script>
@endif
@endsection