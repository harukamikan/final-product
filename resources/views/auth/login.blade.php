<x-guest-layout>

<div class="h-screen flex flex-col md:flex-row">

    {{-- 左側：赤×青グラデーション --}}
    <div class="md:w-1/2 w-full h-1/3 md:h-full bg-gradient-to-br from-blue-400 to-red-400 
                flex items-center justify-center px-6">

        <div class="text-center md:text-left">
            <h1 class="text-white text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">
                Welcome
            </h1>
            <p class="text-white/90 text-lg md:text-xl drop-shadow-md">
                今日も目標達成に向けて頑張りましょう
            </p>
        </div>

    </div>

    {{-- 右側：白背景のログインフォーム --}}
    <div class="md:w-1/2 w-full flex items-center justify-center bg-white">

        <div class="w-full max-w-md p-8 md:p-12 space-y-6">

            {{-- Session Status --}}
            <x-auth-session-status class="mb-4" :status="session('status')" />

            {{-- ログインフォーム --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" 
                                  class="block mt-1 w-full"
                                  type="email"
                                  name="email"
                                  :value="old('email')"
                                  required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                {{-- Password --}}
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" 
                                  class="block mt-1 w-full"
                                  type="password"
                                  name="password"
                                  required />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                           name="remember">

                    <label for="remember_me" class="ml-2 text-sm text-gray-600">
                        {{ __('Remember me') }}
                    </label>
                </div>

                {{-- ボタン行 --}}
                <div class="flex items-center justify-between mt-6">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900"
                           href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <x-primary-button class="px-6 py-2">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>

            {{-- Divider --}}
            <div class="flex items-center my-6">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="px-3 text-gray-500 text-sm">OR</span>
                <div class="flex-grow border-t border-gray-300"></div>
            </div>

            {{-- Slack Login --}}
            <a href="{{ route('slack.login') }}"
               class="w-full flex items-center justify-center gap-3 bg-[#4A154B] text-white
                      font-semibold py-3 rounded-md hover:bg-[#3a0f3d] transition duration-200 shadow">

                {{-- Slack ロゴ --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 122.8 122.8">
                    {{-- Slack Icon Path --}}
                </svg>

                Slackでログイン
            </a>

        </div>
    </div>

</div>

</x-guest-layout>
