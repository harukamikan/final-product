<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'personal', 'description' => '個人ミッション'],
            ['name' => 'company', 'description' => '企業ミッション'],
            ['name' => 'weekly', 'description' => '週間ミッション'],
            ['name' => 'daily', 'description' => '日々のミッション'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['name' => $tag['name']],
                ['description' => $tag['description']]
            );
        }
    }
}