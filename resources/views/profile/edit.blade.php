@extends('layouts.app')

@section('content')
<div class="py-8 px-6">
    <div class="max-w-3xl mx-auto space-y-8">

        {{-- 基本情報 --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">基本情報</h3>

            <p class="text-sm text-gray-600">ユーザー名</p>
            <p class="font-medium mb-4">{{ $user->name }}</p>

            <p class="text-sm text-gray-600">メールアドレス</p>
            <p class="font-medium">{{ $user->email }}</p>
        </div>

        {{-- 表示設定 --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">表示設定</h3>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')


                {{-- 背景色 --}}
                <div>
                    <label for="background_color" class="block text-sm text-gray-600 mb-1">
                        背景色
                    </label>
                    <input type="color" name="background_color" value="{{ auth()->user()->background_color }}">
                </div>

                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                    保存
                </button>
            </form>
        </div>

    </div>
</div>
@endsection