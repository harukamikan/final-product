{{--
  再利用可能なミッションカードコンポーネント
  
  Parameters:
  - $mission: Mission または PersonalMission モデル
  - $userMission: UserMission モデル（進捗情報、オプショナル）
  - $isCompleted: 完了済みかどうか (デフォルト false)
--}}
@php
    $isCompleted = $isCompleted ?? false;
    $type = getMissionType($mission);
    $icon = getMissionIcon($type);
    $actionLabel = getMissionActionLabel($type);
    $colors = getMissionColorClasses($type);
    $url = getMissionUrl($mission);
    
    // 進捗情報
    if (isset($userMission)) {
        $progress = $userMission->progress_count ?? 0;
    } else {
        $progress = $mission->progress_count ?? 0;
    }
    $required = $mission->required_count ?? 1;
    $ratio = min(100, intval($progress / max(1, $required) * 100));
@endphp

@if ($isCompleted)
{{-- 完了済み: クリック不可、hover無し --}}
<div
    class="rounded-3xl border-l-4 {{ $colors['border'] }} bg-white px-5 py-6 shadow-gray-200/50 shadow-md transition-all duration-200 space-y-4"
>
@else
{{-- 進行中: クリック可能 --}}
<div
    role="link"
    tabindex="0"
    onclick="window.location.href='{{ $url }}'"
    onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='{{ $url }}'; }"
    class="group cursor-pointer rounded-3xl border-l-4 {{ $colors['border'] }} bg-white px-5 py-6 shadow-gray-200/50 shadow-md hover:shadow-gray-300/60 hover:shadow-lg hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $colors['ring'] }} transition-all duration-200 space-y-4"
>
@endif
    {{-- タイトルエリア + 円形プログレス --}}
    <div class="flex justify-between items-start gap-4">
        {{-- 左側: アイコン + タイトル --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-3xl leading-none">{{ $icon }}</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $colors['badge'] }}">
                    {{ $actionLabel }}
                </span>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 line-clamp-2">
                {{ $mission->title }}
            </h2>
            @if($mission->description)
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                    {{ $mission->description }}
                </p>
            @endif
        </div>

        {{-- 右側: 円形プログレス --}}
        <div class="flex-shrink-0">
            @include('components.circular_progress', [
                'current' => $progress,
                'required' => $required,
                'colorClass' => $colors['ring'],
                'size' => 64
            ])
        </div>
    </div>

    {{-- 下部エリア: 報酬 or ボタン --}}
    <div class="pt-3 border-t flex justify-between items-center">
        @if ($isCompleted)
            {{-- 完了済みバッジ --}}
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-sm font-medium">
                🎉 達成済み
            </span>
            <div class="text-right">
                <p class="text-xs text-gray-500">獲得マイル</p>
                <p class="text-lg font-bold text-emerald-600">
                    +{{ $mission->reward_miles ?? 0 }} mile
                </p>
            </div>
        @else
            {{-- 進行中: 報酬 + 送信ボタン --}}
            <div class="text-left">
                <p class="text-xs text-gray-500">報酬</p>
                <p class="text-lg font-bold {{ $colors['text'] }}">
                    {{ $mission->reward_miles ?? 0 }} mile
                </p>
            </div>

            {{-- 送信ボタン（stopPropagationでカードクリックを防ぐ） --}}
            @if ($mission instanceof \App\Models\Mission)
                @if ($mission->trigger_type === 'tech_blog_posted')
                    <a
                        href="{{ route('missions.blog-url.form') }}"
                        onclick="event.stopPropagation()"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-medium hover:from-indigo-700 hover:to-indigo-800 transition shadow-sm"
                    >
                        ブログURLを送信
                    </a>
                @elseif ($mission->trigger_type === 'google_form_submitted')
                    <a
                        href="{{ route('missions.form.create', ['mission' => $mission->id]) }}"
                        onclick="event.stopPropagation()"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-medium hover:from-indigo-700 hover:to-indigo-800 transition shadow-sm"
                    >
                        フォームURLを送信
                    </a>
                @endif
            @endif
        @endif
    </div>
</div>
