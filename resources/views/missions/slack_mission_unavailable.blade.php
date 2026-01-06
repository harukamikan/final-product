<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ミッション実行不可</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F6F7FB]">
    <div class="min-h-screen py-10 flex items-center justify-center">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-500 to-gray-600 p-8 text-center">
                    <div class="text-6xl mb-4">⚠️</div>
                    <h1 class="text-2xl font-bold text-white">
                        ミッション実行不可
                    </h1>
                </div>

                <div class="p-8 text-center">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">
                            {{ $message }}
                        </h2>
                        <p class="text-sm text-gray-600">
                            {{ $detail }}
                        </p>
                    </div>

                    <div class="mt-6 p-4 bg-[#F6F7FB] rounded-xl">
                        <p class="text-sm text-gray-700 mb-4">
                            このミッションは現在実行できません。以下の理由が考えられます：
                        </p>
                        <ul class="text-sm text-gray-600 text-left space-y-2 mb-4">
                            <li>• すでに完了済みのミッションです</li>
                            <li>• まだ割り当てられていないミッションです</li>
                            <li>• ミッションの実行条件を満たしていません</li>
                        </ul>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('missions.index') }}" 
                           class="inline-flex items-center gap-2 rounded-xl bg-[#4F47E6] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#3F38D8]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            ミッション一覧を見る
                        </a>
                    </div>

                    <div class="mt-6 text-xs text-gray-500">
                        <p>Webアプリのミッション一覧から実行可能なミッションを確認できます。</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
