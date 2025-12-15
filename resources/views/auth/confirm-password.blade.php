<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-6">
        <div class="w-full max-w-md bg-white rounded-lg shadow p-6">

            {{-- タイトル --}}
            <h2 class="text-lg font-semibold text-gray-800 mb-2">
                パスワードの確認
            </h2>

            {{-- 説明文 --}}
            <p class="text-sm text-gray-600 mb-6">
                アカウント削除などの重要な操作を行うため、  
                セキュリティ上の理由からパスワードの再入力が必要です。
            </p>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                {{-- パスワード入力 --}}
                <div class="mb-4">
                    <label for="password" class="block text-sm text-gray-600 mb-1">
                        現在のパスワード
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- ボタン --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ url()->previous() }}"
                       class="px-4 py-2 text-sm border rounded-md text-gray-600 hover:bg-gray-100">
                        キャンセル
                    </a>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        確認して続行
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>
