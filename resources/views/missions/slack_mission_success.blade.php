<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ミッション達成！</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F6F7FB]">
    <div class="min-h-screen py-10 flex items-center justify-center">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-[#4F47E6] via-[#6F69EA] to-[#918CF0] p-8 text-center">
                    <div class="text-6xl mb-4">🎉</div>
                    <h1 class="text-3xl font-bold text-white">
                        ミッション達成！
                    </h1>
                    <p class="mt-2 text-white/90">
                        おめでとうございます！
                    </p>
                </div>

                <div class="p-8 text-center">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">
                            {{ $mission->title }}
                        </h2>
                        <p class="text-sm text-gray-600">
                            ミッションを達成しました
                        </p>
                    </div>

                    @if ($earned > 0)
                        <div class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-yellow-400 to-orange-400 px-6 py-3 shadow-md">
                            <span class="text-2xl">✨</span>
                            <span class="text-lg font-bold text-white">
                                +{{ $earned }} マイル獲得！
                            </span>
                        </div>
                    @endif

                    <div class="mt-8 p-4 bg-[#F6F7FB] rounded-xl">
                        <p class="text-sm text-gray-600">
                            📱 このウィンドウを閉じて、Slackに戻ってください。<br>
                            💻 Webアプリでマイルや達成履歴を確認できます。
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
