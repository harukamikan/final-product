<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goal App</title>

    {{-- Vite（Tailwind + Alpine + JS） --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen"
    @auth
    @if(auth()->user()->background_type === 'gradient')
    style="background: {{ auth()->user()->background_value }};"
    @else
    style="background-color: {{ auth()->user()->background_value ?? '#f3f4f6' }};"
    @endif
    @else
    style="background-color: #f3f4f6;"
    @endauth
    >
    {{-- ナビゲーション --}}

    @php
    $bgValue = auth()->user()->background_value ?? '#f3f4f6';

    // グラデーションの場合は最初の色を取得
    if (auth()->check() && auth()->user()->background_type === 'gradient') {
    preg_match('/#[0-9A-Fa-f]{6}/', $bgValue, $matches);
    $bgValue = $matches[0] ?? '#f3f4f6';
    }

    // 明るさを計算（RGB → 0-255）
    $r = hexdec(substr($bgValue, 1, 2));
    $g = hexdec(substr($bgValue, 3, 2));
    $b = hexdec(substr($bgValue, 5, 2));
    $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;

    // 明るい背景なら暗い文字、暗い背景なら明るい文字
    $navText = $brightness > 155 ? 'text-white' : 'text-gray-900';
    $navBorder = $brightness > 155 ? 'border-gray-700' : 'border-gray-200';
    $hoverText = $brightness > 155 ? 'hover:text-gray-300' : 'hover:text-gray-700';
    @endphp

    <nav class="backdrop-blur border-b {{ $navBorder }} shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">

                {{-- 管理者メニュー --}}
                <div class="flex space-x-8">
                    <a href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('admin/dashboard') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        📊 ダッシュボード
                    </a>

                    <a href="{{ route('admin.missions.index') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('admin/missions*') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        🎯 ミッション管理
                    </a>

                    <a href="{{ route('admin.rewards.index') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                        {{ request()->is('admin/rewards*') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        🎁 報酬決定
                    </a>

                    <a href="{{ route('admin.reward-distributions.index') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                        {{ request()->is('admin/reward_distributions*') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        🎰 報酬配布管理
                    </a>

                    <a href="{{ route('admin.goals.upload.index') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('admin/goals/upload') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        📊 Excel アップロード
                    </a>

                    <a href="{{ route('admin.goals.ai.index') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('admin/goals/ai-upload') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        🤖 AI 自動抽出
                    </a>
                    <!-- これを追加 -->
                    <a href="{{ route('admin.settings.index') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                    {{ request()->is('admin/settings') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        ⚙️ 設定
                    </a>
                    

                    <a href="/dashboard"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }} {{ $hoverText }}">
                        ← 通常画面に戻る
                    </a>
                </div>

                {{-- 右メニュー（ユーザー） --}}
                <div class="flex items-center">
                    @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center text-sm font-medium {{ $navText }} {{ $hoverText }} focus:outline-none">
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

    <!-- コマンドパレット -->
    <div x-data="{ 
                open: false, 
                search: '',
                init() {
                    document.addEventListener('keydown', (e) => {
                        if (e.ctrlKey && e.key === 'k') {
                            e.preventDefault();
                            this.open = true;
                        }
                        if (e.key === 'Escape') {
                            this.open = false;
                            this.search = '';
                        }
                    });
                }
            }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="open = false; search = ''">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <input
                type="text"
                x-model="search"
                @input="
                            if (search.toLowerCase() === 'admin') {
                                window.location.href = '/admin/dashboard';
                            }
                        "
                placeholder="コマンドを入力..."
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                x-ref="searchInput"
                @click.away="open = false; search = ''">
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</body>

</html>