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

        {{-- セキュリティ設定 --}}
        <div class="bg-white p-6 rounded-lg shadow space-y-6">
            <h3 class="text-lg font-semibold">セキュリティ設定</h3>

            {{-- 成功フラッシュ（3秒で消える） --}}
            @if (session('status') === 'password-updated')
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 3000)"
                    x-show="show"
                    x-transition
                    class="rounded-md bg-green-50 p-4 text-green-700">
                    パスワードを更新しました。
                </div>
            @endif

            {{-- 失敗フラッシュ（3秒で消える） --}}
            @if ($errors->has('current_password') || $errors->has('password'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 3000)"
                    x-show="show"
                    x-transition
                    class="rounded-md bg-red-50 p-4 text-red-700">
                    <p class="font-medium mb-1">パスワードを更新できませんでした</p>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @if ($errors->has('current_password'))
                            <li>現在のパスワードが正しくありません。</li>
                        @endif
                        @if ($errors->has('password'))
                            <li>新しいパスワードを正しく入力してください。</li>
                        @endif
                    </ul>
                </div>
            @endif

            {{-- パスワード変更 --}}
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div>
                    <p class="font-medium mb-2">パスワード変更</p>

                    <div class="space-y-3">
                        <input
                            type="password"
                            name="current_password"
                            placeholder="現在のパスワード"
                            class="w-full rounded px-3 py-2 border
                                @error('current_password') border-red-500 @else border-gray-300 @enderror"
                            required>

                        <input
                            type="password"
                            name="password"
                            placeholder="新しいパスワード"
                            class="w-full rounded px-3 py-2 border
                                @error('password') border-red-500 @else border-gray-300 @enderror"
                            required>

                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="新しいパスワード（確認）"
                            class="w-full rounded px-3 py-2 border
                                @error('password') border-red-500 @else border-gray-300 @enderror"
                            required>
                    </div>

                    <button
                        class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        パスワードを更新
                    </button>
                </div>
            </form>

            <hr>

            {{-- アカウント削除 --}}
            <div>
                <p class="font-medium text-red-600">アカウント削除</p>
                <p class="text-sm text-gray-600 mb-3">
                    この操作は取り消せません。削除前にパスワード確認が必要です。
                </p>

                <a href="{{ route('password.confirm') }}"
                   class="inline-block px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    アカウントを削除する
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
