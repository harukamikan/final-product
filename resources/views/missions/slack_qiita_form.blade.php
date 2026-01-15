<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>技術ブログ(Qiita) - ミッション達成</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F6F7FB]">
    <div class="min-h-screen py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 text-center">
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200">
                    Slack経由でのミッション送信
                </span>
            </div>

            <div class="rounded-2xl bg-gradient-to-r from-[#4F47E6] via-[#6F69EA] to-[#918CF0] p-6 sm:p-8 shadow-md">
                <p class="text-white/90 text-sm font-medium">📝 技術ブログミッション</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-white">
                    {{ $mission->title }}
                </h1>
                <p class="mt-3 text-sm text-white font-medium">
                    Qiitaなどに投稿した技術系ブログの記事URLを入力してください。送信するとミッションの進捗が更新されます。
                </p>
            </div>

            <div class="mt-6 bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="p-6 sm:p-8">
                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
                            <div class="font-semibold text-red-800 mb-2">入力エラーがあります</div>
                            <ul class="list-disc pl-5 text-sm text-red-700 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('slack.missions.qiita.submit') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}" />

                        <div>
                            <label for="url" class="block text-sm font-semibold text-gray-800">
                                記事のURL <span class="text-red-500">*</span>
                            </label>
                            <input id="url" name="url" type="url" required
                                   value="{{ old('url') }}"
                                   placeholder="https://qiita.com/..."
                                   class="mt-2 block w-full rounded-xl border-gray-200 bg-[#F6F7FB]
                                          shadow-sm focus:border-[#4F47E6] focus:ring-[#4F47E6]" />
                            <p class="mt-2 text-xs text-gray-500">
                                💡 送信すると、Qiita APIで記事情報を取得してミッションが完了します
                            </p>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-xl bg-[#4F47E6] px-5 py-2.5 text-sm font-semibold text-white
                                           hover:bg-[#3F38D8] focus:outline-none focus:ring-2 focus:ring-[#4F47E6] focus:ring-offset-2">
                                送信して達成する
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-[#F6F7FB] px-6 sm:px-8 py-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">※ 送信するとQiita APIで記事情報を取得し、マイルが付与されます</p>
                    <p class="text-xs text-gray-500 mt-1">※ このフォームのURLは30分間有効です</p>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
