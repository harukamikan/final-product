{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
    <div class="min-h-screen flex flex-col md:flex-row">

        {{-- 左側：グラデーション --}}
        <div class="md:w-1/2 w-full flex items-center justify-center
                    bg-gradient-to-br from-blue-400 via-purple-500 to-pink-400
                    text-white px-10 py-16">
            <div class="max-w-md text-center">
                <h1 class="text-4xl font-bold mb-4">
                    Create Account
                </h1>
                <p class="text-lg opacity-90">
                    目標達成を一緒に始めましょう
                </p>
            </div>
        </div>

        {{-- 右側：登録フォーム --}}
        <div class="md:w-1/2 w-full flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">

                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                    新規登録
                </h2>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    {{-- Name --}}
                    <div class="mb-4">
                        <x-input-label for="name" value="Name" />
                        <x-text-input
                            id="name"
                            class="block mt-1 w-full"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <x-input-label for="email" value="Email" />
                        <x-text-input
                            id="email"
                            class="block mt-1 w-full"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <x-input-label for="password" value="Password" />
                        <x-text-input
                            id="password"
                            class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-6">
                        <x-input-label for="password_confirmation" value="Confirm Password" />
                        <x-text-input
                            id="password_confirmation"
                            class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"
                            required />
                    </div>

                    {{-- Register Button --}}
                    <div class="flex justify-center mt-6">
                        <button
                            type="submit"
                            class="w-64 bg-indigo-600 text-white py-2 rounded-md
               hover:bg-indigo-700 transition font-semibold text-sm">
                            REGISTER
                        </button>
                    </div>

                </form>

                {{-- OR --}}
                <div class="flex items-center my-6">
                    <div class="flex-grow border-t"></div>
                    <span class="mx-4 text-sm text-gray-500">OR</span>
                    <div class="flex-grow border-t"></div>
                </div>

                {{-- Slack Register --}}
                <div class="flex justify-center">
                    <a
                        href="{{ route('slack.login') }}"
                        class="w-64 text-center bg-purple-700 text-white py-2 rounded-md
               hover:bg-purple-800 transition font-semibold text-sm">
                        Slackで登録
                    </a>
                </div>


                <p class="text-xs text-gray-500 text-center mt-3">
                    Slackで登録すると自動的にアカウントが作成されます
                </p>

                {{-- Login link --}}
                <p class="text-sm text-center text-gray-600 mt-6">
                    すでにアカウントをお持ちですか？
                    <a href="{{ route('login') }}"
                        class="text-indigo-600 font-semibold hover:underline">
                        ログインはこちら
                    </a>
                </p>

            </div>
        </div>
    </div>
</x-guest-layout>