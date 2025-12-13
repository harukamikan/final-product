<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            プロフィール
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto space-y-8">

            {{-- 基本情報 --}}
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">基本情報</h3>

                <div class="space-y-4 text-sm">
                    <div>
                        <span class="text-gray-500">ユーザー名</span>
                        <p class="font-medium">{{ $user->name }}</p>
                    </div>

                    <div>
                        <span class="text-gray-500">メールアドレス</span>
                        <p class="font-medium">{{ $user->email }}</p>
                    </div>
                </div>
            </div>

            {{-- Slack連携 --}}
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">外部連携</h3>

                @if ($user->slack_id)
                <p class="text-green-600 font-medium">
                    ✅ Slack連携済み
                </p>
                @else
                <p class="text-gray-500">
                    未連携
                </p>

                <a href="{{ route('slack.login') }}"
                    class="inline-block mt-3 text-indigo-600 hover:underline">
                    Slackと連携する
                </a>
                @endif
            </div>

            {{-- テーマ設定 --}}
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">表示設定</h3>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            背景テーマ
                        </label>

                        <select name="theme"
                            class="w-full border-gray-300 rounded-md">
                            <option value="light" {{ $user->theme === 'light' ? 'selected' : '' }}>
                                ライト
                            </option>
                            <option value="dark" {{ $user->theme === 'dark' ? 'selected' : '' }}>
                                ダーク
                            </option>
                        </select>
                    </div>

                    {{-- 表示設定 --}}
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">表示設定</h3>

                        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            {{-- 背景色 --}}
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">
                                    背景色
                                </label>

                                <input
                                    type="color"
                                    name="background_color"
                                    value="{{ $user->background_color ?? '#667eea' }}"
                                    class="w-20 h-10 p-0 border rounded cursor-pointer" />
                            </div>

                            <button
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                                保存
                            </button>
                        </form>
                    </div>

            </div>
        </div>
</x-app-layout>