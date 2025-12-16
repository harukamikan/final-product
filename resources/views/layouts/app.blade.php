<!DOCTYPE html>
<html lang="ja"
    style="background-color: {{ $bgColor }};">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goal App</title>

    {{-- Vite（Tailwind + Alpine + JS） --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen"
    style="background-color: {{ $bgColor }};">
    {{-- ナビゲーション --}}
    <nav class="bg-white/80 backdrop-blur border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">

                {{-- 左メニュー --}}
                <div class="flex space-x-8">
                    <a href="/dashboard"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium
                       {{ request()->is('dashboard') ? 'text-gray-900 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        ホーム
                    </a>

                    <a href="/missions"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium
                       {{ request()->is('missions*') ? 'text-gray-900 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        ミッション
                    </a>

                    <a href="/stats"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium
                       {{ request()->is('stats*') ? 'text-gray-900 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        統計
                    </a>

                    <a href="/activities"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium
                       {{ request()->is('activities*') ? 'text-gray-900 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        活動記録
                    </a>

                    <a href="/ranking"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium
                       {{ request()->is('ranking*') ? 'text-gray-900 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        ランキング
                    </a>
                </div>

                {{-- 右メニュー（ユーザー） --}}
                <div class="flex items-center">
                    @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                            <span>{{ auth()->user()->name }}</span>
                            <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open"
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                            <a href="{{ route('profile.edit') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                プロフィール
                            </a>

                            <!-- 半期目標AI取得 -->
                            <div class="border-t border-gray-200"></div>
                            <a href="{{ route('admin.goals.upload.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                📊 Excel アップロード
                            </a>
                            <a href="{{ route('admin.goals.ai.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                🤖 AI 抽出
                            </a>
                            <!-- ここまで -->
                            <a href="/logout"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                ログアウト
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    </div>
                    @endauth
                </div>

            </div>
        </div>
    </nav>

    {{-- フラッシュメッセージ --}}
    @if (session('status') === 'account-deleted')
    <div
        x-data="{ show: true }"
        x-init="
            setTimeout(() => show = false, 2500);
            setTimeout(() => window.location.href = '/', 3000);
        "
        x-show="show"
        x-transition
        class="mx-auto max-w-3xl mt-4 rounded-md bg-green-50 p-4 text-green-700 text-center">
        <p class="font-medium">アカウントを削除しました。</p>
        <p class="text-sm mt-1">トップページへ移動します…</p>
    </div>
    @endif




    {{-- メインコンテンツ --}}
    <main class="min-h-screen px-6 py-6">
        @yield('content')
    </main>

</body>

</html>