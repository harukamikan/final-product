<?php

use App\Models\Company;
use App\Models\User;
use App\Models\TimelineEvent;

beforeEach(function () {
    // テスト用会社とユーザーを作成
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Test User',
    ]);
    $this->otherUser = User::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Other User',
    ]);

    // 他の会社のユーザーを作成（マルチテナントテスト用）
    $otherCompany = Company::factory()->create();
    $this->otherCompanyUser = User::factory()->create([
        'company_id' => $otherCompany->id,
    ]);
});

test('it can search timeline events by keyword', function () {
    // テストデータ作成
    TimelineEvent::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'event_type' => 'qiita',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'Laravel Testing Tips',
            'url' => 'https://qiita.com/test/laravel-testing',
            'summary' => 'How to test Laravel applications',
        ],
    ]);

    TimelineEvent::create([
        'company_id' => $this->company->id,
        'user_id' => $this->otherUser->id,
        'event_type' => 'qiita',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'React Hooks Guide',
            'url' => 'https://qiita.com/test/react-hooks',
            'summary' => 'Understanding React Hooks',
        ],
    ]);

    // キーワード検索テスト
    $response = $this->actingAs($this->user)
        ->get(route('timeline.index', ['keyword' => 'Laravel']));

    $response->assertStatus(200);
    $response->assertSee('Laravel Testing Tips');
    $response->assertDontSee('React Hooks Guide');
});

test('it can filter timeline events by user', function () {
    // テストデータ作成
    TimelineEvent::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'event_type' => 'qiita',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'User Event',
            'url' => 'https://qiita.com/test/user-event',
        ],
    ]);

    TimelineEvent::create([
        'company_id' => $this->company->id,
        'user_id' => $this->otherUser->id,
        'event_type' => 'qiita',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'Other User Event',
            'url' => 'https://qiita.com/test/other-user-event',
        ],
    ]);

    // ユーザーフィルターテスト
    $response = $this->actingAs($this->user)
        ->get(route('timeline.index', ['user_id' => $this->user->id]));

    $response->assertStatus(200);
    $response->assertSee('User Event');
    $response->assertDontSee('Other User Event');
});

test('it can combine keyword and user filters', function () {
    // テストデータ作成
    TimelineEvent::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'event_type' => 'event_hosting',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'Laravel Meetup',
            'details' => 'Monthly Laravel meetup',
        ],
    ]);

    TimelineEvent::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'event_type' => 'event_hosting',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'React Conference',
            'details' => 'Annual React conference',
        ],
    ]);

    TimelineEvent::create([
        'company_id' => $this->company->id,
        'user_id' => $this->otherUser->id,
        'event_type' => 'event_hosting',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'Laravel Workshop',
            'details' => 'Beginner Laravel workshop',
        ],
    ]);

    // 複合検索テスト (keyword: Laravel AND user: $this->user)
    $response = $this->actingAs($this->user)
        ->get(route('timeline.index', [
            'keyword' => 'Laravel',
            'user_id' => $this->user->id,
        ]));

    $response->assertStatus(200);
    $response->assertSee('Laravel Meetup');
    $response->assertDontSee('React Conference');
    $response->assertDontSee('Laravel Workshop');
});

test('it only shows events from same company', function () {
    // 自社のイベント
    TimelineEvent::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'event_type' => 'qiita',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'Own Company Event',
            'url' => 'https://qiita.com/test/own-event',
        ],
    ]);

    // 他社のイベント
    TimelineEvent::create([
        'company_id' => $this->otherCompanyUser->company_id,
        'user_id' => $this->otherCompanyUser->id,
        'event_type' => 'qiita',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'Other Company Event',
            'url' => 'https://qiita.com/test/other-event',
        ],
    ]);

    // マルチテナントテスト
    $response = $this->actingAs($this->user)
        ->get(route('timeline.index'));

    $response->assertStatus(200);
    $response->assertSee('Own Company Event');
    $response->assertDontSee('Other Company Event');
});

test('it only shows users from same company in filter', function () {
    $response = $this->actingAs($this->user)
        ->get(route('timeline.index'));

    $response->assertStatus(200);
    // 同じ会社のユーザーは表示される
    $response->assertSee($this->user->name);
    $response->assertSee($this->otherUser->name);
    // 他の会社のユーザーは表示されない
    $response->assertDontSee($this->otherCompanyUser->name);
});

test('it shows search active indicators when filtering', function () {
    TimelineEvent::create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'event_type' => 'qiita',
        'occurred_at' => now(),
        'payload' => [
            'title' => 'Test Event',
            'url' => 'https://qiita.com/test',
        ],
    ]);

    // キーワード検索時
    $response = $this->actingAs($this->user)
        ->get(route('timeline.index', ['keyword' => 'Test']));
    $response->assertStatus(200);
    $response->assertSee('検索条件:');
    $response->assertSee('キーワード: "Test"');
    
    // ユーザーフィルター時
    $response = $this->actingAs($this->user)
        ->get(route('timeline.index', ['user_id' => $this->user->id]));
    $response->assertStatus(200);
    $response->assertSee('検索条件:');
    $response->assertSee('メンバー: ' . $this->user->name);
});

test('it can clear search filters', function () {
    $response = $this->actingAs($this->user)
        ->get(route('timeline.index', [
            'keyword' => 'test',
            'user_id' => $this->user->id,
        ]));

    $response->assertStatus(200);
    // クリアボタンが表示される
    $response->assertSee('クリア');
});
