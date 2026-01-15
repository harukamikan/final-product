<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halfway</title>

    <link rel="icon" href="/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">

    {{-- Vite（Tailwind + Alpine） --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen"
    @auth
        @if(auth()->user()->background_type === 'gradient')
            style="background: {{ auth()->user()->background_value }};"
        @else
            style="background-color: {{ auth()->user()->background_value ?? config('app.default_background_color') }};"
        @endif
    @else
        style="background-color: {{ config('app.default_background_color') }};"
    @endauth
>

@php
    $bgValue = auth()->user()->background_value ?? config('app.default_background_color');

    if (auth()->check() && auth()->user()->background_type === 'gradient') {
        preg_match('/#[0-9A-Fa-f]{6}/', $bgValue, $matches);
        $bgValue = $matches[0] ?? config('app.default_background_color');
    }

    $r = hexdec(substr($bgValue, 1, 2));
    $g = hexdec(substr($bgValue, 3, 2));
    $b = hexdec(substr($bgValue, 5, 2));
    $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;

    $navText   = $brightness > 155 ? 'text-gray-900' : 'text-white';
    $navBorder = $brightness > 155 ? 'border-gray-200' : 'border-gray-700';
    $hoverText = $brightness > 155 ? 'hover:text-gray-700' : 'hover:text-gray-300';
@endphp

{{-- ================= ナビゲーション ================= --}}
<nav class="fixed top-0 left-0 right-0 z-50 backdrop-blur border-b {{ $navBorder }} shadow-sm"
     x-data="{ mobileMenuOpen: false }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- ハンバーガー（SP） --}}
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden {{ $navText }} focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- 左メニュー（PC） --}}
            <div class="hidden md:flex space-x-8">
                <a href="/dashboard"  class="nav-link {{ request()->is('dashboard') ? 'border-b-2 border-indigo-500' : $hoverText }}">ホーム</a>
                <a href="/missions"   class="nav-link {{ request()->is('missions*') ? 'border-b-2 border-indigo-500' : $hoverText }}">ミッション</a>
                <a href="/stats"      class="nav-link {{ request()->is('stats*') ? 'border-b-2 border-indigo-500' : $hoverText }}">統計</a>
                <a href="/activities" class="nav-link {{ request()->is('activities*') ? 'border-b-2 border-indigo-500' : $hoverText }}">活動履歴</a>
                <a href="{{ route('timeline.index') }}" class="nav-link {{ request()->is('timeline*') ? 'border-b-2 border-indigo-500' : $hoverText }}">タイムライン</a>
                <a href="/ranking"    class="nav-link {{ request()->is('ranking*') ? 'border-b-2 border-indigo-500' : $hoverText }}">ランキング</a>
                <a href="{{ route('rewards.gacha') }}" class="nav-link {{ request()->is('rewards/gacha*') ? 'border-b-2 border-indigo-500' : $hoverText }}">ガチャ</a>
                <a href="{{ route('rewards.my') }}" class="nav-link {{ request()->is('rewards/my*') ? 'border-b-2 border-indigo-500' : $hoverText }}">有効報酬</a>
            </div>

            {{-- 右メニュー --}}
            @auth
            <div class="flex items-center gap-4">

                {{-- ユーザー名（長押し対応） --}}
                <div class="relative" x-data="{ open: false }">
                    <button
                        class="flex items-center text-sm font-medium {{ $navText }} {{ $hoverText }} focus:outline-none select-none"
                        @click="open = !open"

                        @can('admin')
                            x-data="longPressAdmin()"
                            @pointerdown.prevent.stop="start"
                            @pointerup.stop="cancel"
                            @pointerleave.stop="cancel"
                            @pointercancel.stop="cancel"
                            @contextmenu.prevent
                            style="-webkit-touch-callout:none; touch-action:none;"
                        @endcan
                    >
                        <span>{{ auth()->user()->name }}</span>
                        <svg class="ml-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">プロフィール</a>
                        <a href="{{ route('documents.specification') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">仕様書📥︎</a>
                        <a href="/logout"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="block px-4 py-2 text-sm hover:bg-gray-100">ログアウト</a>
                    </div>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>

            </div>
            @endauth

        </div>
    </div>

    {{-- モバイルメニュー --}}
    <div x-show="mobileMenuOpen"
         @click.away="mobileMenuOpen = false"
         class="md:hidden fixed top-0 left-0 h-screen w-64 bg-white shadow-xl z-50 overflow-y-auto">
        <div class="p-4 space-y-2">
            <a href="/dashboard" class="block py-2">ホーム</a>
            <a href="/missions" class="block py-2">ミッション</a>
            <a href="/stats" class="block py-2">統計</a>
            <a href="/activities" class="block py-2">活動履歴</a>
            <a href="{{ route('timeline.index') }}" class="block py-2">タイムライン</a>
            <a href="/ranking" class="block py-2">ランキング</a>
            <a href="{{ route('rewards.gacha') }}" class="block py-2">ガチャ</a>
            <a href="{{ route('rewards.my') }}" class="block py-2">有効報酬</a>
        </div>
    </div>
</nav>

{{-- ================= メイン ================= --}}
<main class="pt-20 px-6 py-6 min-h-screen">
    @yield('content')
</main>

{{-- ================= コマンドパレット ================= --}}
<div
    x-data="{
        open:false,
        search:'',
        init(){
            document.addEventListener('keydown',(e)=>{
                if((e.ctrlKey||e.metaKey)&&e.key==='k'){
                    e.preventDefault();this.open=true;
                }
                if(e.key==='Escape'){this.open=false;this.search='';}
            });
            window.addEventListener('open-command-palette',()=>{
                this.open=true;
                this.$nextTick(()=>this.$refs.search.focus());
            });
        }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    @click.self="open=false;search='';"
>
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
        <input
            x-ref="search"
            x-model="search"
            @input="if(search.toLowerCase()==='admin'){window.location.href='/admin/dashboard'}"
            placeholder="コマンドを入力..."
            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500"
        >
    </div>
</div>

<style>[x-cloak]{display:none!important}</style>

<script>
function longPressAdmin(){
    let timer=null;
    return{
        start(){
            timer=setTimeout(()=>{
                window.dispatchEvent(new Event('open-command-palette'));
            },600);
        },
        cancel(){ clearTimeout(timer); }
    }
}
</script>

</body>
</html>
