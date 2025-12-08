<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mission;

class MissionSeeder extends Seeder
{
    public function run(): void
{
    Mission::create([
        'code' => 'WRITE_TECH_BLOG',
        'title' => '技術系ブログを1本書く',
        'description' => 'Qiita や Zenn に技術記事を1つ投稿する',
        'reward_miles' => 50,
    ]);
}

}
