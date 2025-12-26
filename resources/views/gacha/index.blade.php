@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto text-center space-y-6">

    <h1 class="text-2xl font-bold">🎰 ガチャ</h1>

    @if($canDrawGacha)
        <form method="POST" action="{{ route('gacha.draw') }}">
            @csrf
            <button class="px-6 py-3 bg-indigo-600 text-white rounded-lg">
                ガチャを引く
            </button>
        </form>
    @else
        <p class="text-gray-500">
            マイルが不足しています。<br>
            ミッションを達成してマイルを獲得しましょう！
        </p>
    @endif

</div>
@endsection
