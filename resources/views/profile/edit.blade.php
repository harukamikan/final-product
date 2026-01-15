@extends('layouts.app')

@section('content')
<div class="py-8 px-6">
    <div class="max-w-3xl mx-auto space-y-8">

        {{-- ================= 基本情報 ================= --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">基本情報</h3>

            <p class="text-sm text-gray-600">ユーザー名</p>
            <p class="font-medium mb-4">{{ $user->name }}</p>

            <p class="text-sm text-gray-600">メールアドレス</p>
            <p class="font-medium mb-4">{{ $user->email }}</p>

            @if($user->company)
            <p class="text-sm text-gray-600">所属会社</p>
            <p class="font-medium mb-4">{{ $user->company->name }}</p>
            @endif
            <!-- Slack ID 編集フォーム -->
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label for="slack_id" class="block text-sm font-medium text-gray-700">
                        Slack User ID
                    </label>
                    <input type="text" name="slack_id" id="slack_id"
                        value="{{ old('slack_id', $user->slack_id) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <p class="mt-1 text-sm text-gray-500">
                        Slack ID（例: U01234ABCDE）を入力してください
                    </p>
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    保存
                </button>
            </form>
            <p class="text-sm text-gray-600">ニックネーム</p>
            <div class="border rounded p-3" x-data="{ open: false }">
                <div class="flex items-center justify-between">
                    <p class="font-medium">{{ $user->nickname ?? '未設定' }}</p>
                    <button @click="open = true" class="text-indigo-600 hover:text-indigo-700">
                        ✏️
                    </button>
                </div>


                {{-- モーダル --}}
                <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="open = false">
                    <div class="bg-white rounded-lg shadow p-6 w-96" @click.stop x-data="{ nicknameInput: '{{ $user->nickname ?? '' }}' }">
                        <h3 class="text-lg font-semibold mb-4">プレイヤー名</h3>

                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PATCH')

                            <input
                                type="text"
                                name="nickname"
                                x-model="nicknameInput"
                                placeholder="例：素早い猫"
                                maxlength="8"
                                class="w-full rounded px-3 py-2 border border-gray-300 mb-2">

                            <p class="text-xs text-gray-500 mb-4">8文字まで入力できます</p>

                            <div class="flex gap-2 mb-4">
                                <button
                                    type="button"
                                    @click="
                                        const adjectives = ['素早い', '賢い', '勇敢な', '静かな', '輝く', '優しい', '強い', '美しい', '清い', '深い'];
                                        const nouns = ['猫', '狼', '竜', '鳥', '月', '星', '風', '火', '水', '木', '石', '雲', '波', '光', '影', '夢', '音', '香'];
                                        const adj = adjectives[Math.floor(Math.random() * adjectives.length)];
                                        const noun = nouns[Math.floor(Math.random() * nouns.length)];
                                        nicknameInput = (adj + noun).substring(0, 8);
                                    "
                                    class="flex-1 px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                                    🎲 ランダム
                                </button>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    @click="open = false"
                                    class="flex-1 px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                                    キャンセル
                                </button>
                                <button
                                    type="submit"
                                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                    OK
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= 通知設定 ================= --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">通知設定</h3>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                <!-- リマインド通知ON/OFF -->
                <div class="space-y-3">
                    <!-- 全体のON/OFF -->
                    <div>
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                name="reminder_enabled"
                                value="1"
                                {{ old('reminder_enabled', $user->reminder_enabled) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">
                                リマインド通知を受け取る
                            </span>
                        </label>
                        <p class="mt-1 ml-8 text-xs text-gray-500">
                            すべてのリマインド通知を受け取るかどうかの設定です
                        </p>
                    </div>

                    <!-- 期限リマインド -->
                    <div class="ml-8">
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                name="reminder_deadline_enabled"
                                value="1"
                                {{ old('reminder_deadline_enabled', $user->reminder_deadline_enabled) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">
                                期限リマインドを受け取る
                            </span>
                        </label>
                        <p class="mt-1 ml-8 text-xs text-gray-500">
                            半期目標の期限が近づいたときの通知
                        </p>
                    </div>

                    <!-- 週次リマインド -->
                    <div class="ml-8">
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                name="reminder_weekly_enabled"
                                value="1"
                                {{ old('reminder_weekly_enabled', $user->reminder_weekly_enabled) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">
                                週次リマインドを受け取る
                            </span>
                        </label>
                        <p class="mt-1 ml-8 text-xs text-gray-500">
                            毎週の進捗確認の通知
                        </p>
                    </div>
                </div>

                <!-- 期限リマインドのタイミング -->
                <div class="pt-4 border-t">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        期限リマインドのタイミング
                    </label>
                    <select
                        name="reminder_days_before"
                        class="rounded-md border-gray-300 text-sm">
                        <option value="3" {{ old('reminder_days_before', $user->reminder_days_before) == 7 ? 'selected' : '' }}>
                            3日前
                        </option>
                        <option value="5" {{ old('reminder_days_before', $user->reminder_days_before) == 5 ? 'selected' : '' }}>
                            5日前
                        </option>
                        <option value="7" {{ old('reminder_days_before', $user->reminder_days_before) == 3 ? 'selected' : '' }}>
                            7日前
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        半期目標の期限から何日前に通知を受け取るか選択できます（月初めと1日前は自動で届きます）
                    </p>
                </div>

                <!-- 週次リマインドの設定 -->
                <div class="pt-4 border-t" x-data="{ 
                    frequency: '{{ old('reminder_frequency', $user->reminder_frequency ?? 'weekly') }}',
                    selectedDays: {{ json_encode(old('reminder_days', json_decode($user->reminder_days ?? '[]', true))) }}
                }">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        週次リマインド
                    </label>

                    <!-- 頻度選択 -->
                    <div class="mb-4">
                        <label class="block text-xs text-gray-500 mb-2">頻度</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="reminder_frequency"
                                    value="daily"
                                    x-model="frequency"
                                    class="text-indigo-600">
                                <span class="text-sm">毎日</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="reminder_frequency"
                                    value="weekly"
                                    x-model="frequency"
                                    class="text-indigo-600">
                                <span class="text-sm">特定の曜日</span>
                            </label>
                        </div>
                    </div>

                    <!-- 曜日選択（weekly の時だけ表示） -->
                    <div x-show="frequency === 'weekly'" class="mb-4">
                        <label class="block text-xs text-gray-500 mb-2">曜日を選択</label>
                        <div class="flex flex-wrap gap-2">
                            @php
                            $days = ['日', '月', '火', '水', '木', '金', '土'];
                            @endphp
                            @foreach($days as $index => $day)
                            <label class="flex items-center gap-1">
                                <input
                                    type="checkbox"
                                    name="reminder_days[]"
                                    value="{{ $index }}"
                                    x-model="selectedDays"
                                    class="rounded text-indigo-600">
                                <span class="text-sm">{{ $day }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 時間選択 -->
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">時間</label>
                        <select
                            name="reminder_hour"
                            class="rounded-md border-gray-300 text-sm">
                            @for($i = 0; $i < 24; $i++)
                                <option value="{{ $i }}" {{ old('reminder_hour', $user->reminder_hour) == $i ? 'selected' : '' }}>
                                {{ sprintf('%02d:00', $i) }}
                                </option>
                                @endfor
                        </select>
                    </div>

                    <p class="mt-2 text-xs text-gray-500" x-show="frequency === 'daily'">
                        毎日指定した時間に進捗確認の通知を受け取ります
                    </p>
                    <p class="mt-2 text-xs text-gray-500" x-show="frequency === 'weekly'">
                        選択した曜日の指定した時間に進捗確認の通知を受け取ります
                    </p>
                </div>

                <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 hover:bg-blue-700 text-white font-bold rounded">
                    保存
                </button>
            </form>
        </div>

        {{-- ================= 表示設定 ================= --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">表示設定</h3>

            <form
                method="POST"
                action="{{ route('profile.update') }}"
                x-data="{
                    type: '{{ $user->background_type ?? 'color' }}',

                    // 単色
                    color: '{{ $user->background_type === 'color'
                        ? ($user->background_value ?? config('app.default_background_color'))
                        : config('app.default_background_color') }}',

                    defaultColor: '{{ config('app.default_background_color') }}',

                    // グラデーション（最大3色）
                    grad1: '#667eea',
                    grad2: '#764ba2',
                    grad3: '#38f9d7',
                    useGrad3: false,
                    gradAngle: '135deg',

                    get gradient() {
                        return this.useGrad3
                            ? `linear-gradient(${this.gradAngle}, ${this.grad1}, ${this.grad2}, ${this.grad3})`
                            : `linear-gradient(${this.gradAngle}, ${this.grad1}, ${this.grad2})`
                    }
                }">
                @csrf
                @method('PATCH')

                {{-- 背景タイプ --}}
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">背景タイプ</label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2">
                            <input type="radio" value="color" x-model="type">
                            単色
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" value="gradient" x-model="type">
                            グラデーション
                        </label>
                    </div>
                </div>

                {{-- 単色選択 --}}
                <div x-show="type === 'color'" class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">背景色</label>

                    <div class="flex items-center gap-3">
                        <input
                            type="color"
                            x-model="color"
                            class="h-10 w-16 cursor-pointer">

                        <button
                            type="button"
                            @click="color = defaultColor"
                            class="px-3 py-2 text-sm border rounded-md text-gray-600 hover:bg-gray-100">
                            初期色に戻す
                        </button>
                    </div>
                </div>

                {{-- グラデーション（最大3色） --}}
                <div x-show="type === 'gradient'" class="mb-4 space-y-4">

                    <label class="block text-sm text-gray-600">
                        グラデーションカラー（最大3色）
                    </label>

                    <div class="flex items-center gap-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">色①</p>
                            <input type="color" x-model="grad1" class="h-10 w-16">
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 mb-1">色②</p>
                            <input type="color" x-model="grad2" class="h-10 w-16">
                        </div>

                        <div x-show="useGrad3">
                            <p class="text-xs text-gray-500 mb-1">色③</p>
                            <input type="color" x-model="grad3" class="h-10 w-16">
                        </div>
                    </div>

                    {{-- 3色目 ON/OFF --}}
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" x-model="useGrad3">
                        3色目を使う
                    </label>

                    {{-- 方向 --}}
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">方向</label>
                        <select x-model="gradAngle" class="rounded-md border-gray-300 text-sm">
                            <option value="90deg">左 → 右</option>
                            <option value="135deg">左上 → 右下</option>
                            <option value="180deg">上 → 下</option>
                            <option value="45deg">右上 → 左下</option>
                        </select>
                    </div>
                </div>

                {{-- プレビュー --}}
                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-1">プレビュー</p>
                    <div
                        class="h-24 rounded-lg border"
                        :style="
                            type === 'gradient'
                                ? `background: ${gradient}`
                                : `background-color: ${color}`
                        "></div>
                </div>

                {{-- hidden --}}
                <input type="hidden" name="background_type" :value="type">
                <input
                    type="hidden"
                    name="background_value"
                    :value="type === 'color' ? color : gradient">

                <button class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    保存
                </button>
            </form>
        </div>

        {{-- ================= チーム招待 ================= --}}
        @if ($inviteLink)
        <div class="bg-white p-6 rounded-lg shadow space-y-4" x-data="{ regenerateModalOpen: false }">
            <h3 class="text-lg font-semibold">チーム招待</h3>

            {{-- 成功メッセージ --}}
            @if (session('invite_regenerated'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 3000)"
                x-show="show"
                x-transition
                class="rounded-md bg-green-50 p-4 text-green-700">
                {{ session('invite_regenerated') }}
            </div>
            @endif

            {{-- エラーメッセージ --}}
            @if ($errors->has('invite'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                x-transition
                class="rounded-md bg-red-50 p-4 text-red-700">
                {{ $errors->first('invite') }}
            </div>
            @endif

            <p class="text-sm text-gray-600">
                下の招待リンクをコピーしてメンバーに共有してください。
                リンクから登録すると自動でこの会社に所属します。
            </p>

            <div class="space-y-2">
                <p class="text-sm font-medium text-gray-700">招待リンク</p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <input
                        id="inviteLink"
                        type="text"
                        value="{{ $inviteLink }}"
                        readonly
                        class="flex-1 rounded-2xl border-gray-200 bg-gray-50 text-sm">

                    <button
                        type="button"
                        onclick="copyInviteLink()"
                        class="shrink-0 rounded-2xl bg-indigo-600 text-white px-5 py-3 font-semibold hover:bg-indigo-700 transition">
                        コピー
                    </button>
                </div>

                <p id="copyToast" class="hidden text-sm text-emerald-600 font-medium">
                    コピーしました！
                </p>
            </div>

            {{-- 再生成ボタン --}}
            <div class="pt-2">
                <button
                    type="button"
                    @click="regenerateModalOpen = true"
                    class="px-4 py-2 text-sm border rounded-md text-gray-600 hover:bg-gray-100">
                    🔄 招待リンクを更新
                </button>
                <p class="text-xs text-gray-500 mt-2">
                    ※ 更新は5分間に1回のみ可能です
                </p>
            </div>

            {{-- 再生成確認モーダル --}}
            <div
                x-show="regenerateModalOpen"
                x-transition
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
                @click="regenerateModalOpen = false">
                <div class="bg-white rounded-lg shadow p-6 w-full max-w-md" @click.stop>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        招待リンクを更新しますか？
                    </h3>

                    <p class="text-sm text-gray-600 mb-4">
                        招待リンクを更新します。既存のリンクはそのまま有効です。
                    </p>

                    <div class="flex justify-end gap-3">
                        <button
                            @click="regenerateModalOpen = false"
                            class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                            キャンセル
                        </button>

                        <form method="POST" action="{{ route('profile.regenerate-invite') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                更新する
                            </button>
                        </form>
                    </div>
                </div>
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
        @endif

        {{-- ================= セキュリティ設定 ================= --}}
        <div class="bg-white p-6 rounded-lg shadow space-y-6">
            <h3 class="text-lg font-semibold">セキュリティ設定</h3>

            {{-- 成功フラッシュ --}}
            @if (session('status') === 'password-updated')
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 3000)"
                x-show="show"
                x-transition
                class="rounded-md bg-green-50 p-4 text-green-700">
                パスワードを更新しました。
            </div>
            @endif

            @if ($errors->updatePassword->any())
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 3000)"
                x-show="show"
                x-transition
                class="rounded-md bg-red-50 p-4 text-red-700">
                <p class="font-medium mb-1">パスワードを更新できませんでした</p>
                <ul class="list-disc pl-5 text-sm space-y-1">
                    @if ($errors->updatePassword->has('current_password'))
                    <li>現在のパスワードが正しくありません。</li>
                    @endif
                    @if ($errors->updatePassword->has('password'))
                    <li>新しいパスワードを正しく入力してください。</li>
                    @endif
                </ul>
            </div>
            @endif

            {{-- パスワード変更 --}}
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="space-y-3">
                    <input
                        type="password"
                        name="current_password"
                        placeholder="現在のパスワード"
                        class="w-full rounded px-3 py-2 border
                        @error('current_password', 'updatePassword') border-red-500 @else border-gray-300 @enderror"
                        required>

                    <input
                        type="password"
                        name="password"
                        placeholder="新しいパスワード"
                        class="w-full rounded px-3 py-2 border
                        @error('password', 'updatePassword') border-red-500 @else border-gray-300 @enderror"
                        required>

                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="新しいパスワード（確認）"
                        class="w-full rounded px-3 py-2 border border-gray-300"
                        required>
                </div>

                <button class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    パスワードを更新
                </button>
            </form>

            <hr>

            {{-- アカウント削除 --}}
            <div x-data="{ open: false }">
                <p class="font-medium text-red-600">アカウント削除</p>
                <p class="text-sm text-gray-600 mb-3">
                    この操作は取り消せません。削除前にパスワード確認が必要です。
                </p>

                <button
                    @click="open = true"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    アカウントを削除する
                </button>

                <div
                    x-show="open"
                    x-transition
                    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow p-6 w-full max-w-md">
                        <h3 class="text-lg font-semibold text-red-600 mb-2">
                            本当に削除しますか？
                        </h3>

                        <p class="text-sm text-gray-600 mb-4">
                            アカウントを削除すると、すべてのデータが完全に削除されます。
                        </p>

                        <div class="flex justify-end gap-3">
                            <button
                                @click="open = false"
                                class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                                キャンセル
                            </button>


                            <a href="{{ route('profile.delete.confirm') }}"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                削除する
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection