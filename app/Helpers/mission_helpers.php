<?php

/**
 * ミッション種別判定・表示ヘルパー関数
 */

if (!function_exists('getMissionType')) {
    /**
     * mission.key から種別を判定
     *
     * @param \App\Models\Mission|\App\Models\PersonalMission $mission
     * @return string 'tech_blog'|'event_speaking'|'event_hosting'|'certification'|'unknown'
     */
    function getMissionType($mission): string
    {
        $key = $mission->key ?? '';

        // keyマッピング
        $mapping = [
            'write_tech_blog' => 'tech_blog',
            'qiita' => 'tech_blog',
            'tech_blog' => 'tech_blog',

            'event_speaker' => 'event_speaking',
            'speak_event' => 'event_speaking',
            'manual_event_speaker' => 'event_speaking',

            'event_organizer' => 'event_hosting',
            'event_hosting' => 'event_hosting',
            'plan_event' => 'event_hosting',
            'manual_event_owner' => 'event_hosting',

            'acquire_certificate' => 'certification',
            'certification' => 'certification',
            'get_cert' => 'certification',
            'manual_certification' => 'certification',
        ];

        return $mapping[$key] ?? 'unknown';
    }
}

if (!function_exists('getMissionIcon')) {
    /**
     * 種別に応じたアイコン絵文字を返す
     *
     * @param string $type
     * @return string
     */
    function getMissionIcon(string $type): string
    {
        return match ($type) {
            'tech_blog' => '✍️',
            'event_speaking' => '🎤',
            'event_hosting' => '🗓️',
            'certification' => '📘',
            default => '🎯',
        };
    }
}

if (!function_exists('getMissionActionLabel')) {
    /**
     * 種別に応じた動詞ラベルを返す
     *
     * @param string $type
     * @return string
     */
    function getMissionActionLabel(string $type): string
    {
        return match ($type) {
            'tech_blog' => '書く',
            'event_speaking' => '登壇する',
            'event_hosting' => '企画する',
            'certification' => '学ぶ',
            default => 'クリア',
        };
    }
}

if (!function_exists('getMissionColorClasses')) {
    /**
     * 種別に応じたTailwindクラスを返す
     *
     * @param string $type
     * @return array{border: string, badge: string, ring: string, text: string}
     */
    function getMissionColorClasses(string $type): array
    {
        return match ($type) {
            'tech_blog' => [
                'border' => 'border-indigo-10',
                'badge' => 'bg-indigo-50 text-indigo-700',
                'ring' => 'text-indigo-600',
                'text' => 'text-indigo-600',
            ],
            'event_speaking' => [
                'border' => 'border-purple-300',
                'badge' => 'bg-purple-50 text-purple-700',
                'ring' => 'text-purple-600',
                'text' => 'text-purple-600',
            ],
            'event_hosting' => [
                'border' => 'border-amber-300',
                'badge' => 'bg-amber-50 text-amber-700',
                'ring' => 'text-amber-600',
                'text' => 'text-amber-600',
            ],
            'certification' => [
                'border' => 'border-emerald-500',
                'badge' => 'bg-emerald-50 text-emerald-700',
                'ring' => 'text-emerald-600',
                'text' => 'text-emerald-600',
            ],
            default => [
                'border' => 'border-gray-400',
                'badge' => 'bg-gray-50 text-gray-700',
                'ring' => 'text-gray-600',
                'text' => 'text-gray-600',
            ],
        };
    }
}

if (!function_exists('getMissionUrl')) {
    /**
     * ミッションに応じた送信/詳細URLを返す
     *
     * @param \App\Models\Mission|\App\Models\PersonalMission $mission
     * @return string
     */
    function getMissionUrl($mission): string
    {
        // PersonalMission の場合は編集ページへ
        if ($mission instanceof \App\Models\PersonalMission) {
            return route('missions.personal.edit', $mission->id);
        }

        // Mission の trigger_type に応じて分岐
        if ($mission->trigger_type === 'tech_blog_posted') {
            return route('missions.blog-url.form');
        }

        if ($mission->trigger_type === 'google_form_submitted') {
            return route('missions.form.create', ['mission' => $mission->id]);
        }

        // その他の場合はデフォルトでミッション詳細（存在しない場合は index へ）
        return route('missions.index');
    }
}
