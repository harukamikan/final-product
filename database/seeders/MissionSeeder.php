<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mission;

class MissionSeeder extends Seeder
{
    public function run()
    {
        Mission::updateOrCreate(
            ['key' => 'write_qiita_article'],
            [
                'title'          => 'Qiitaに技術ブログを1本書く',
                'description'    => 'Qiitaに記事を投稿し、URLを登録すると達成となります。',
                'trigger_type'   => 'tech_blog_posted',
                'required_count' => 1,
                'reward_miles'   => 50,
                'repeatable'     => true,  // 記事1本ごとにマイル欲しければ true
            ]
        );

        Mission::updateOrCreate(
            ['key' => 'speak_at_event'],
            [
                'title'          => 'イベントに登壇する',
                'description'    => '社内外の勉強会・カンファレンスなどで登壇したら達成ボタンを押してください。',
                'trigger_type'   => 'manual_event_speaker',
                'required_count' => 1,
                'reward_miles'   => 200,
                'repeatable'     => true, // 登壇のたびに達成OK
            ]
        );

        Mission::updateOrCreate(
            ['key' => 'organize_event'],
            [
                'title'          => 'イベントを企画・開催する',
                'description'    => '勉強会やLT会などを企画・主催したら達成ボタンを押してください。',
                'trigger_type'   => 'manual_event_owner',
                'required_count' => 1,
                'reward_miles'   => 300,
                'repeatable'     => true,
            ]
        );

        Mission::updateOrCreate(
            ['key' => 'get_certification'],
            [
                'title'          => '資格を取得する',
                'description'    => '業務に関連する資格を取得したら達成ボタンを押してください。',
                'trigger_type'   => 'manual_certification',
                'required_count' => 1,
                'reward_miles'   => 500,
                'repeatable'     => true, // 資格ごとにマイル付与
            ]
        );
    }
}