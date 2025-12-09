{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goal App</title>

    {{-- Tailwind CDN（簡易UI表示用） --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<!-- ========== ここから追加 ========== -->
    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex space-x-8">
                    <a href="/dashboard" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-indigo-500">
                        ホーム
                    </a>
                    <a href="/missions" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        ミッション
                    </a>
                    <a href="/stats" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        統計
                    </a>
                    <a href="/activities" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        活動記録
                    </a>
                </div>
                <div class="flex items-center">
                    @auth
                        <span class="text-gray-700 mr-4">{{ auth()->user()->name }}</span>
                        <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-sm text-gray-500 hover:text-gray-700">
                            ログアウト
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="min-h-screen">
        @yield('content')
    </div>

</body>
</html>