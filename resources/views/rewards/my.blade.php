@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">
    <h1 class="text-2xl font-bold text-slate-800">
        🎁 所持している報酬
    </h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if($userRewards->isEmpty())
        <div class="bg-white rounded-xl p-6 shadow text-gray-500">
            現在、使用可能な報酬はありません。
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($userRewards as $ur)
                <div class="bg-white rounded-xl p-5 shadow space-y-3">
                    <div class="font-semibold text-lg">
                        {{ $ur->reward->name }}
                    </div>
                    {{-- 有効期限 --}}
                    @if($ur->expires_at)
                        <p class="text-sm text-gray-500">
                            ⏰ 有効期限：
                            <span class="font-medium">
                                {{ $ur->expires_at->format('Y/m/d H:i') }}
                            </span>
                        </p>
                    @else
                        <p class="text-sm text-gray-400">
                            ⏰ 有効期限：なし
                        </p>
                    @endif
                    {{-- 使うボタン --}}
                    <form method="POST" action="{{ route('rewards.use', $ur->id) }}" 
                          onsubmit="return confirm('「{{ $ur->reward->name }}」を使用しますか？\n管理者に通知されます。')">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition">
                            🎉 使う
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection