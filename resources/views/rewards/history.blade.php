@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">🎁 報酬履歴</h1>

    <div class="space-y-4">
        @forelse($histories as $history)
            <div class="bg-white rounded-xl shadow p-4 flex justify-between items-center">

                {{-- 左側 --}}
                <div>
                    <p class="font-semibold text-lg">
                        {{ $history['icon'] }} {{ $history['title'] }}
                    </p>

                    <p class="text-sm text-gray-500">
                        {{ $history['type'] === 'gacha' ? '🎰 ガチャ' : '🪙 スクラッチ' }}
                        ｜取得日：{{ $history['created_at']->format('Y/m/d H:i') }}
                    </p>
                </div>

                {{-- 右側 --}}
                <div class="text-right">
                    @if($history['expires_at'])
                        <p class="text-sm text-gray-400">有効期限</p>
                        <p class="text-sm font-medium text-red-500">
                            {{ $history['expires_at']->format('Y/m/d') }}
                        </p>
                    @else
                        <p class="text-sm text-gray-400">有効期限なし</p>
                    @endif
                </div>

            </div>
        @empty
            <p class="text-gray-500">まだ取得履歴はありません。</p>
        @endforelse
    </div>
</div>
@endsection
