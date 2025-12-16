<x-guest-layout>

    <div class="h-screen flex flex-col md:flex-row">

        {{-- 左側 --}}
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

        {{-- 右側 --}}
        <div class="md:w-1/2 w-full flex items-center justify-center bg-white">
            <div class="w-full max-w-md p-8 md:p-12 space-y-6">

                <x-auth-session-status :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" class="mt-1 w-full"
                            type="email" name="email" required autofocus />
                    </div>

                    {{-- Password --}}
                    <div>
                        <x-input-label for="password" value="Password" />
                        <x-text-input id="password" class="mt-1 w-full"
                            type="password" name="password" required />
                    </div>

                    {{-- Remember / Forgot --}}
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember"
                                class="rounded border-gray-300 text-indigo-600">
                            Remember me
                        </label>

                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="hover:text-gray-900 underline">
                            Forgot password?
                        </a>
                        @endif
                    </div>

                    {{-- LOG IN --}}
                    <div class="flex justify-end">
                        <x-primary-button class="w-1/3 justify-center py-2">
                            Log in
                        </x-primary-button>
                    </div>

                </form>

                {{-- Divider --}}
                <div class="flex items-center gap-3">
                    <div class="flex-grow border-t"></div>
                    <span class="text-sm text-gray-400">OR</span>
                    <div class="flex-grow border-t"></div>
                </div>

                {{-- Slack --}}
                <div class="flex justify-end">
                    <a href="{{ route('slack.login') }}"
                        class="w-1/3 flex items-center justify-center gap-2
              bg-[#4A154B] text-white text-sm font-semibold
              py-2 rounded-md hover:bg-[#3a0f3d] shadow">
                        Slackでログイン
                    </a>
            </div>


        </div>
    </div>
    </div>

</x-guest-layout>