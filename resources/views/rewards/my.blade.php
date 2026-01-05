@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

    <h1 class="text-2xl font-bold text-slate-800">
        🎁 所持している報酬
    </h1>

    @if($userRewards->isEmpty())
        <div class="bg-white rounded-xl p-6 shadow text-gray-500">
            現在、使用可能な報酬はありません。
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($userRewards as $ur)
                <div class="bg-white rounded-xl p-5 shadow space-y-2">

                    <div class="font-semibold text-lg">
                        {{ $ur->reward->name }}
                    </div>

                    @if($ur->reward->description)
                        <p class="text-sm text-gray-600">
                            {{ $ur->reward->description }}
                        </p>
                    @endif

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

                    {{-- 状態 --}}
                    <div class="text-xs text-green-600 font-semibold">
                        使用可能
                    </div>

                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
