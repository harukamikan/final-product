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

            <form
                method="POST"
                action="{{ route('profile.update') }}"
                x-data="{
        defaultColor: '{{ config('app.default_background_color', '#f3f4f6') }}',
        color: '{{ auth()->user()->background_color ?? config('app.default_background_color', '#f3f4f6') }}'
    }">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm text-gray-600 mb-1">
                        背景色
                    </label>

                    <div class="flex items-center gap-3">
                        <input
                            type="color"
                            x-model="color"
                            class="h-10 w-16 cursor-pointer">

                        <button
                            type="button"
                            @click="color = defaultColor"
                            class="px-3 py-2 text-sm border rounded-md text-gray-600 hover:bg-gray-100">
                            初期色に戻す
                        </button>
                    </div>
                </div>

                <input type="hidden" name="background_color" :value="color">

                <button class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md">
                    保存
                </button>
            </form>

        </div>

    </div>
</div>
@endsection