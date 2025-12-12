@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- 左側：テキスト情報 --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- ミッション種別 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">
                ミッション種別
            </label>
            <p class="text-xs text-gray-500 mt-1">
                ミッションのパターンを選択してください。内部的なキーやトリガーは自動で設定されます。
            </p>
            @php
                $missionTypeSelected = old('mission_type', isset($mission) ? $mission->key : '');
            @endphp
            <select
                name="mission_type"
                class="mt-2 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">選択してください</option>
                <option value="write_tech_blog" @selected($missionTypeSelected === 'write_tech_blog')>
                    技術系ブログ（Qiita）を書く
                </option>
                <option value="event_speaker" @selected($missionTypeSelected === 'event_speaker')>
                    イベントに登壇する
                </option>
                <option value="event_organizer" @selected($missionTypeSelected === 'event_organizer')>
                    イベントを企画・開催する
                </option>
                <option value="acquire_certificate" @selected($missionTypeSelected === 'acquire_certificate')>
                    資格を取得する
                </option>
            </select>
            @error('mission_type')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- タイトル --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">
                タイトル
            </label>
            <p class="text-xs text-gray-500 mt-1">
                管理画面やユーザーに表示されるミッション名です。
            </p>
            <input
                type="text"
                name="title"
                value="{{ old('title', $mission->title ?? '') }}"
                placeholder="例: 技術ブログ（Qiita）を書く"
                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
            @error('title')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- 説明 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">
                説明
            </label>
            <p class="text-xs text-gray-500 mt-1">
                達成条件の詳細や注意事項などがあれば入力してください。
            </p>
            <textarea
                name="description"
                rows="4"
                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >{{ old('description', $mission->description ?? '') }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- 右側：条件 / 報酬設定（前に作ったやつそのまま使ってOK） --}}
    <div class="space-y-6">
        <div class="rounded-2xl border bg-gray-50 px-4 py-4 space-y-4">

            {{-- 必要回数 --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    必要回数
                </label>
                <input
                    type="number"
                    name="required_count"
                    min="1"
                    value="{{ old('required_count', $mission->required_count ?? 1) }}"
                    class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                @error('required_count')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 報酬マイル --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    報酬マイル
                </label>
                <div class="mt-2 relative rounded-xl shadow-sm">
                    <input
                        type="number"
                        name="reward_miles"
                        min="0"
                        value="{{ old('reward_miles', $mission->reward_miles ?? 0) }}"
                        class="block w-full rounded-xl border-gray-300 pr-10 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <span class="text-gray-400 text-xs">mile</span>
                    </div>
                </div>
                @error('reward_miles')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 繰り返し --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    達成タイプ
                </label>
                @php
                    $repeatable = (int) old('repeatable', isset($mission) ? $mission->repeatable : 0);
                @endphp
                <div class="mt-2 space-y-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="repeatable" value="0" @checked($repeatable === 0)>
                        <span>一度だけ達成できる</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="repeatable" value="1" @checked($repeatable === 1)>
                        <span>何度も達成できる</span>
                    </label>
                </div>
                @error('repeatable')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.missions.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                キャンセル
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                保存する
            </button>
        </div>
    </div>

</div>
