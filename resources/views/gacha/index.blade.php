{{-- ============================= --}}
{{-- ガチャ / スクラッチ --}}
{{-- ============================= --}}
<div class="space-y-4 pt-2">

    {{-- 🎰 ガチャ --}}
    <form method="POST" action="{{ route('rewards.play.gacha') }}">
        @csrf
        <button
            @disabled(!$canDrawGacha)
            class="w-full py-3 rounded-xl font-bold text-lg shadow-md transition
                {{ $canDrawGacha
                    ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white hover:opacity-90'
                    : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">
            🎰 ガチャを引く
        </button>
        <p class="text-xs mt-1 text-slate-600">
            消費：{{ $gachaCost }} マイル
            @unless($canDrawGacha)
            <span class="text-red-500">
                （あと {{ $gachaCost - $totalMiles }} マイル不足）
            </span>
            @endunless
        </p>
    </form>

    {{-- 🪙 スクラッチ --}}
    <form method="POST" action="{{ route('rewards.play.scratch') }}">
        @csrf
        <button
            @disabled(!$canDrawScratch)
            class="w-full py-3 rounded-xl font-bold text-lg shadow-md transition
                {{ $canDrawScratch
                    ? 'bg-gradient-to-r from-amber-400 to-orange-400 text-white hover:opacity-90'
                    : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">
            🪙 スクラッチを削る
        </button>
        <p class="text-xs mt-1 text-slate-600">
            消費：{{ $gachaCost }} マイル
            @if(!$canDrawGacha)
            （あと {{ $gachaCost - $totalMiles }} マイル不足）
            @endif
        </p>
    </form>

</div>