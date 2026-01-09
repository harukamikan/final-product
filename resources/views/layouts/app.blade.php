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

    // 明るい背景なら暗いナビゲーション、暗い背景なら明るいナビゲーション
    $navText = $brightness > 155 ? 'text-white' : 'text-gray-900';
    $navBorder = $brightness > 155 ? 'border-gray-700' : 'border-gray-200';
    $hoverText = $brightness > 155 ? 'hover:text-gray-300' : 'hover:text-gray-700';
    @endphp

    <nav class="backdrop-blur border-b {{ $navBorder }} shadow-sm" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                {{-- ハンバーガーボタン（スマホのみ表示） --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden {{ $navText }} focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- 左メニュー（PC表示） --}}
                <div class="hidden md:flex space-x-8">
                    <a href="/dashboard"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('dashboard') && !request()->is('*/') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        ホーム
                    </a>

                    <a href="/missions"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('missions*') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        ミッション
                    </a>

                    <a href="/stats"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('stats*') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        統計
                    </a>

                    <a href="/activities"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('activities*') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        活動履歴
                    </a>

                    {{-- タイムライン（統合） --}}
                    <a href="{{ route('timeline.index') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('timeline*') || request()->is('qiita*') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        タイムライン
                    </a>

                    <a href="/ranking"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
                       {{ request()->is('ranking*') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        ランキング
                    </a>

                    {{-- 🎰 ガチャ --}}
                    <a href="{{ route('rewards.gacha') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
   {{ request()->is('rewards/gacha*') || request()->is('rewards/play/*')
        ? 'border-b-2 border-indigo-500'
        : $hoverText }}">
                        ガチャ
                    </a>

                    {{-- 🎁 有効報酬 --}}
                    <a href="{{ route('rewards.my') }}"
                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ $navText }}
   {{ request()->is('rewards/my*') ? 'border-b-2 border-indigo-500' : $hoverText }}">
                        有効報酬
                    </a>
                </div>

                {{-- 右メニュー（ユーザー） --}}
                <div class="flex items-center">
                    @auth
                    {{-- 通知ベル --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="
                        open = !open;
                        if (open) {
                                fetch('{{ route('notifications.mark-all-read') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json'
                                    }
                                }).then(() => {
                                    setTimeout(() => location.reload(), 500);
                                });
                            }
                        "
                            class="relative {{ $navText }} {{ $hoverText }} focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>

                            @php
                            $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
                            ->where('is_read', false)
                            ->count();
                            @endphp

                            @if($unreadCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $unreadCount }}
                            </span>
                            @endif
                        </button>

                        {{-- 通知ドロップダウン --}}
                        <div x-show="open"
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-10 max-h-96 overflow-y-auto">

                            @php
                            $notifications = \App\Models\Notification::where('user_id', auth()->id())
                            ->where('is_read', false)
                            ->orderBy('created_at', 'desc')
                            ->get();
                            @endphp

                            @if($notifications->isEmpty())
                            <p class="px-4 py-3 text-sm text-gray-500 text-center">
                                通知はありません
                            </p>
                            @else
                            @foreach($notifications as $notification)
                            <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
                                <p class="text-sm text-gray-700">
                                    {{ $notification->message }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
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

        {{-- モバイルメニュー --}}
        <div x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            @click.away="mobileMenuOpen = false"
            class="md:hidden fixed top-0 left-0 h-screen w-64 @if($brightness > 155) bg-gray-800 @else bg-white @endif border-r {{ $navBorder }} shadow-xl z-50 overflow-y-auto">
            <div class="h-full px-2 pt-2 pb-3 space-y-1 @if($brightness > 155) bg-gray-800 @else bg-white @endif">
                <a href="/dashboard"
                    class="block px-3 py-2 rounded-md text-base font-medium {{ $navText }} {{ $hoverText }}
                           {{ request()->is('dashboard') ? 'bg-indigo-500 bg-opacity-20' : '' }}">
                    ホーム
                </a>
                <a href="/missions"
                    class="block px-3 py-2 rounded-md text-base font-medium @if($brightness > 155) text-white hover:bg-gray-700 @else text-gray-900 hover:bg-gray-100 @endif
                           {{ request()->is('missions*') ? 'bg-indigo-500 bg-opacity-20' : '' }}">
                    ミッション
                </a>
                <a href="/stats"
                    class="block px-3 py-2 rounded-md text-base font-medium @if($brightness > 155) text-white hover:bg-gray-700 @else text-gray-900 hover:bg-gray-100 @endif
                           {{ request()->is('stats*') ? 'bg-indigo-500 bg-opacity-20' : '' }}">
                    統計
                </a>
                <a href="/activities"
                    class="block px-3 py-2 rounded-md text-base font-medium @if($brightness > 155) text-white hover:bg-gray-700 @else text-gray-900 hover:bg-gray-100 @endif
                           {{ request()->is('activities*') ? 'bg-indigo-500 bg-opacity-20' : '' }}">
                    活動履歴
                </a>
                <a href="{{ route('timeline.index') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium @if($brightness > 155) text-white hover:bg-gray-700 @else text-gray-900 hover:bg-gray-100 @endif
                           {{ request()->is('timeline*') || request()->is('qiita*') ? 'bg-indigo-500 bg-opacity-20' : '' }}">
                    タイムライン
                </a>
                <a href="/ranking"
                    class="block px-3 py-2 rounded-md text-base font-medium @if($brightness > 155) text-white hover:bg-gray-700 @else text-gray-900 hover:bg-gray-100 @endif
                           {{ request()->is('ranking*') ? 'bg-indigo-500 bg-opacity-20' : '' }}">
                    ランキング
                </a>
                <a href="{{ route('rewards.gacha') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium @if($brightness > 155) text-white hover:bg-gray-700 @else text-gray-900 hover:bg-gray-100 @endif
                           {{ request()->is('rewards/gacha*') ? 'bg-indigo-500 bg-opacity-20' : '' }}">
                    ガチャ
                </a>
                <a href="{{ route('rewards.my') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium
   @if($brightness > 155)
       text-white hover:bg-gray-700
   @else
       text-gray-900 hover:bg-gray-100
   @endif
   {{ request()->is('rewards/my*') ? 'bg-indigo-500 bg-opacity-20' : '' }}">
                    有効報酬
                </a>

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