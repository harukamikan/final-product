@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">マイルランキング</h1>

    {{-- 自分の順位 --}}
    <div class="mb-6 p-4 bg-yellow-100 rounded-lg">
        <p class="text-lg font-semibold">
            あなたの順位： <span class="text-red-500">{{ $myRank }} 位</span>
        </p>
        <p>現在の累計マイル：{{ auth()->user()->total_miles }} mile</p>
    </div>

    {{-- ランキング一覧 --}}
    <div class="space-y-4">
        @foreach ($rankers as $index => $user)
            <div class="p-4 bg-white border rounded-lg flex items-center justify-between">

                <div class="flex items-center gap-4">
                    {{-- 順位 --}}
                    <span class="text-xl font-bold w-10 text-center">
                        {{ $index + 1 }}
                    </span>

                    {{-- アバター --}}
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" class="w-10 h-10 rounded-full">
                    @else
                        <div class="w-10 h-10 bg-gray-300 rounded-full"></div>
                    @endif

                    {{-- ユーザー名 --}}
                    <div>
                        <p class="font-semibold">{{ $user->name }}</p>
                    </div>
                </div>

                {{-- 合計マイル --}}
                <span class="text-lg font-bold">{{ $user->total_miles }} mile</span>
            </div>
        @endforeach
    </div>

</div>
@endsection
