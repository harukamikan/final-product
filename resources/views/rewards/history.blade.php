@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">🎁 取得履歴</h1>

    <div class="space-y-4">
        @forelse($histories as $history)
            <div class="bg-white rounded-xl shadow p-4 flex justify-between">
                <div>
                    <p class="font-semibold">
                        {{ $history->reward->name }}
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ $history->via === 'gacha' ? '🎰 ガチャ' : '🪙 スクラッチ' }}
                        ｜{{ $history->created_at->format('Y/m/d H:i') }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-gray-500">まだ取得履歴はありません。</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $histories->links() }}
    </div>
</div>
@endsection
