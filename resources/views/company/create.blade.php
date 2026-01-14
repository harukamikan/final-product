@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="w-full max-w-xl space-y-6">

        {{-- ===== 招待リンク表示（会社作成直後） ===== --}}
        @if (session('invite_link'))
            <div class="rounded-3xl border bg-white shadow-sm p-8 space-y-6">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">会社を作成しました 🎉</h1>
                        <p class="text-sm text-gray-500 mt-1">
                            下の招待リンクをコピーしてメンバーに共有してください。
                            リンクから登録すると自動でこの会社に所属します。
                        </p>
                    </div>
                    <div class="text-3xl">🔗</div>
                </div>

                @if (session('success'))
                    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">招待リンク</p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <input
                            id="inviteLink"
                            type="text"
                            value="{{ session('invite_link') }}"
                            readonly
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 text-sm"
                        />

                        <button
                            type="button"
                            onclick="copyInviteLink()"
                            class="shrink-0 rounded-2xl bg-indigo-600 text-white px-5 py-3 font-semibold hover:bg-indigo-700 transition"
                        >
                            コピー
                        </button>
                    </div>

                    <p id="copyToast" class="hidden text-sm text-emerald-600 font-medium">
                        コピーしました！
                    </p>

                    <p class="text-xs text-gray-500">
                        ※ 共有先がSlackの場合は、そのまま貼り付けでOKです。
                    </p>
                </div>

                <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-end">
                    <a href="{{ route('company.join') }}"
                       class="rounded-2xl bg-gray-100 text-gray-700 px-5 py-3 font-semibold hover:bg-gray-200 transition text-center>
                        招待コードで参加する人はこちら
                    </a>

                    <a href="{{ route('dashboard') }}"
                       class="rounded-2xl bg-gray-900 text-white px-5 py-3 font-semibold hover:bg-gray-800 transition text-center">
                        ダッシュボードへ
                    </a>
                </div>

                <script>
                    function copyInviteLink() {
                        const input = document.getElementById('inviteLink');
                        const text = input.value;

                        // Clipboard API が使える環境
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(text).then(() => {
                                const toast = document.getElementById('copyToast');
                                toast.classList.remove('hidden');
                                setTimeout(() => toast.classList.add('hidden'), 1500);
                            });
                            return;
                        }

                        // フォールバック（http/local など）
                        input.select();
                        input.setSelectionRange(0, 99999);
                        document.execCommand('copy');

                        const toast = document.getElementById('copyToast');
                        toast.classList.remove('hidden');
                        setTimeout(() => toast.classList.add('hidden'), 1500);
                    }
                </script>

            </div>

        {{-- ===== 通常：会社作成フォーム ===== --}}
        @else
            <div class="rounded-3xl border bg-white shadow-sm p-8 space-y-6">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">会社を作成</h1>
                        <p class="text-sm text-gray-500 mt-1">
                            会社を作成すると、招待リンクを発行できます。メンバーはリンクから登録すると自動で参加できます。
                        </p>
                    </div>
                    <div class="text-3xl">🏢</div>
                </div>

                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>・{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('company.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="text-sm font-medium text-gray-700">会社名</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="例）HalfWay / ○○開発部"
                            class="mt-2 w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                    </div>



                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-indigo-600 text-white py-3 font-semibold hover:bg-indigo-700 transition"
                    >
                        会社を作成して招待リンクを発行
                    </button>


                </form>

            </div>
        @endif

    </div>
</div>
@endsection
