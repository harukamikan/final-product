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
                        ? ($user->background_value ?? '#f3f4f6')
                        : '#f3f4f6' }}',
                    defaultColor: '{{ config('app.default_background_color', '#f3f4f6') }}',

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
                }"
            >
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
                        "
                    ></div>
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

                            
                                href="{{ route('profile.delete.confirm') }}"
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