<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Divider -->
    <div class="flex items-center my-6">
        <div class="flex-grow border-t border-gray-300"></div>
        <span class="px-3 text-gray-500 text-sm">OR</span>
        <div class="flex-grow border-t border-gray-300"></div>
    </div>

    <!-- Slack Login Button -->
    <div class="flex justify-center">
        <a href="{{ route('slack.login') }}"
            class="w-full flex items-center justify-center gap-3 bg-[#4A154B] text-white font-semibold py-2 px-4 rounded-md hover:bg-[#3a0f3d] transition duration-200">

            <!-- Slack Logo -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 122.8 122.8">
                <path fill="#E01E5A" d="M30.3 76.7c0 5.6-4.6 10.2-10.2 10.2S0 82.3 0 76.7s4.6-10.2 10.2-10.2h20.1v10.2zm5.1 0c0-5.6 4.6-10.2 10.2-10.2s10.2 4.6 10.2 10.2v25.5c0 5.6-4.6 10.2-10.2 10.2s-10.2-4.6-10.2-10.2V76.7z"/>
                <path fill="#36C5F0" d="M46.8 30.3C41.2 30.3 36.6 25.7 36.6 20.1S41.2 9.9 46.8 9.9s10.2 4.6 10.2 10.2v20.1H46.8zm0 5.1c5.6 0 10.2 4.6 10.2 10.2s-4.6 10.2-10.2 10.2H21.3c-5.6 0-10.2-4.6-10.2-10.2s4.6-10.2 10.2-10.2h25.5z"/>
                <path fill="#2EB67D" d="M92.5 46.8c5.6 0 10.2 4.6 10.2 10.2s-4.6 10.2-10.2 10.2H72.4V56.9c0-5.6 4.6-10.2 10.2-10.2h9.9zm-5.1 0c0-5.6-4.6-10.2-10.2-10.2s-10.2 4.6-10.2 10.2v25.5c0 5.6 4.6 10.2 10.2 10.2s10.2-4.6 10.2-10.2V46.8z"/>
                <path fill="#ECB22E" d="M76.7 92.5c0-5.6 4.6-10.2 10.2-10.2s10.2 4.6 10.2 10.2-4.6 10.2-10.2 10.2H76.7V92.5zm-5.1 0c0 5.6-4.6 10.2-10.2 10.2s-10.2-4.6-10.2-10.2V67c0-5.6 4.6-10.2 10.2-10.2s10.2 4.6 10.2 10.2v25.5z"/>
            </svg>

            Slackでログイン
        </a>
    </div>

</x-guest-layout>
