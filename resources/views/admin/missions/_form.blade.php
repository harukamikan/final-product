{{-- resources/views/admin/missions/_form.blade.php --}}
@csrf

<div class="space-y-5 text-sm">
    <div>
        <label class="block text-gray-700 text-xs font-semibold">
            ミッションキー
        </label>
        <input type="text"
               name="key"
               value="{{ old('key', $mission->key ?? '') }}"
               class="mt-1 w-full rounded-xl border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
               placeholder="write_qiita_article">
        @error('key')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-gray-700 text-xs font-semibold">
            タイトル
        </label>
        <input type="text"
               name="title"
               value="{{ old('title', $mission->title ?? '') }}"
               class="mt-1 w-full rounded-xl border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
               placeholder="Qiitaに技術ブログを1本書く">
        @error('title')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-gray-700 text-xs font-semibold">
            説明
        </label>
        <textarea
            name="description"
            rows="3"
            class="mt-1 w-full rounded-xl border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            placeholder="ミッションの詳細説明を記入してください。">{{ old('description', $mission->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-gray-700 text-xs font-semibold">
                トリガー種別
            </label>
            @php
                $selected = old('trigger_type', $mission->trigger_type ?? 'tech_blog_posted');
            @endphp
            <select name="trigger_type"
                    class="mt-1 w-full rounded-xl border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="tech_blog_posted" @selected($selected === 'tech_blog_posted')>
                    技術ブログ（Qiita）
                </option>
                <option value="manual_event_speaker" @selected($selected === 'manual_event_speaker')>
                    イベント登壇（手動）
                </option>
                <option value="manual_event_owner" @selected($selected === 'manual_event_owner')>
                    イベント企画（手動）
                </option>
                <option value="manual_certification" @selected($selected === 'manual_certification')>
                    資格取得（手動）
                </option>
            </select>
            @error('trigger_type')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-gray-700 text-xs font-semibold">
                必要回数
            </label>
            <input type="number" min="1"
                   name="required_count"
                   value="{{ old('required_count', $mission->required_count ?? 1) }}"
                   class="mt-1 w-full rounded-xl border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            @error('required_count')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-gray-700 text-xs font-semibold">
                報酬マイル
            </label>
            <input type="number" min="0"
                   name="reward_miles"
                   value="{{ old('reward_miles', $mission->reward_miles ?? 0) }}"
                   class="mt-1 w-full rounded-xl border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            @error('reward_miles')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-gray-700 text-xs font-semibold">
                達成タイプ
            </label>
            @php
                $repeat = (int) old('repeatable', isset($mission) ? $mission->repeatable : 0);
            @endphp
            <div class="mt-1 flex flex-col gap-1 text-xs">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="repeatable" value="0" @checked($repeat === 0)>
                    <span>一度きり</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="repeatable" value="1" @checked($repeat === 1)>
                    <span>繰り返し達成できる</span>
                </label>
            </div>
            @error('repeatable')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-2">
        <a href="{{ route('admin.missions.index') }}"
           class="px-4 py-2 rounded-full border border-gray-200 text-xs text-gray-600 hover:bg-gray-50">
            キャンセル
        </a>
        <button type="submit"
                class="px-5 py-2 rounded-full bg-purple-600 text-xs font-semibold text-white hover:bg-purple-700">
            保存する
        </button>
    </div>
</div>
