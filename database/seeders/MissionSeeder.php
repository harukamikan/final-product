<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mission;

class MissionSeeder extends Seeder
{
    public function run()
    {
         // 1. 技術ブログ（Qiita）
        Mission::updateOrCreate(
            ['key' => 'write_tech_blog'],
            [
                'title'          => '技術ブログ（Qiita）を書く',
                'description'    => 'Qiitaなどに技術記事を投稿し、URLを登録すると達成となります。',
                'trigger_type'   => 'tech_blog_posted',
                'required_count' => 1,
                'reward_miles'   => 50,   // ★会社と相談して決める
                'repeatable'     => false,
            ]
        );

        // 2. イベントに登壇する
        Mission::updateOrCreate(
            ['key' => 'event_speaker'],
            [
                'title'          => 'イベントに登壇する',
                'description'    => '技術イベントなどで登壇し、報告用のGoogleフォームURLを登録すると達成となります。',
                'trigger_type'   => 'google_form_submitted',
                'required_count' => 1,
                'reward_miles'   => 150, // TODO: 適宜調整
                'repeatable'     => false,
            ]
        );

        // 3. イベントを企画・開催する
        Mission::updateOrCreate(
            ['key' => 'event_organizer'],
            [
                'title'          => 'イベントを企画・開催する',
                'description'    => '社内外向けのイベントを企画・開催し、報告用のGoogleフォームURLを登録すると達成となります。',
                'trigger_type'   => 'google_form_submitted',
                'required_count' => 1,
                'reward_miles'   => 200, // TODO: 適宜調整
                'repeatable'     => false,
            ]
        );

        // 4. 資格を取得する
        Mission::updateOrCreate(
            ['key' => 'acquire_certificate'],
            [
                'title'          => '資格を取得する',
                'description'    => '業務やキャリアに関連する資格を取得し、報告用のGoogleフォームURLを登録すると達成となります。',
                'trigger_type'   => 'google_form_submitted',
                'required_count' => 1,
                'reward_miles'   => 100, // TODO: 資格の重さで調整してもOK
                'repeatable'     => false,
            ]
        );
    }
}