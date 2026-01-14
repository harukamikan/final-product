<x-guest-layout>
    <div class="h-screen flex flex-col md:flex-row">

        {{-- 左側：グラデーション（マイル → ミッション） --}}
        <div class="md:w-1/2 w-full h-1/3 md:h-full
                    bg-gradient-to-br from-blue-500 via-sky-400 to-yellow-400
                    flex items-center justify-center px-6">
            <div class="text-center md:text-left">
                <h1 class="text-white text-3xl md:text-5xl font-bold mb-4 drop-shadow-lg">
                    Password Reset
                </h1>
                <p class="text-white/90 text-base md:text-lg drop-shadow-md max-w-md">
                    登録したメールアドレスを入力してください。<br>
                    パスワード再設定用のリンクをお送りします。
                </p>
            </div>
        </div>

        {{-- 右側：フォーム --}}
        <div class="md:w-1/2 w-full flex items-center justify-center bg-white">
            <div class="w-full max-w-md p-8 md:p-12 space-y-6">

                {{-- 説明文 --}}
                <div class="text-sm text-gray-600 leading-relaxed">
                    パスワードをお忘れですか？  
                    メールアドレスを入力すると、新しいパスワードを設定するための
                    リンクをお送りします。
                </div>

                {{-- Session Status --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input
                            id="email"
                            class="mt-1 w-full"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- 送信ボタン --}}
                    <div class="flex justify-center">
                        <x-primary-button class="w-64 justify-center py-2 text-sm">
                            Email Password Reset Link
                        </x-primary-button>
                    </div>
                </form>

                {{-- 戻るリンク --}}
                <div class="text-center text-sm text-gray-600">
                    <a href="{{ route('login') }}"
                       class="text-indigo-600 font-medium hover:underline">
                        ログイン画面に戻る
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>
