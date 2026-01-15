<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Mission;
use App\Services\QiitaService;
use App\Services\GeminiService;
use App\Services\MissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Mission Controller', function () {
    beforeEach(function () {
        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);
        
        // SemesterSettingを作成（MileHistory作成時に必要）
        $this->semester = \App\Models\SemesterSetting::create([
            'company_id' => $this->company->id,
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addMonths(3),
            'auto_reset_enabled' => true,
        ]);
        
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->mission = Mission::factory()->writeTechBlog()->create([
            'company_id' => null,
            'user_id' => null,
        ]);
    });

    describe('showBlogUrlForm', function () {
        it('displays blog URL form', function () {
            $response = $this->actingAs($this->user)
                ->get(route('missions.blog-url.form'));

            $response->assertStatus(200);
            $response->assertViewIs('missions.blog_url');
            $response->assertViewHas('mission');

            $viewMission = $response->viewData('mission');
            expect($viewMission->key)->toBe('write_tech_blog');
        });
    });

    describe('submitBlogUrl', function () {
        it('submits Qiita URL and awards miles', function () {
            // QiitaServiceとGeminiServiceをモック化
            $qiitaService = Mockery::mock(QiitaService::class);
            $qiitaService->shouldReceive('fetchItemFromUrl')
                ->once()
                ->andReturn([
                    'item_id' => 'test123',
                    'title' => 'Test Article',
                    'body' => 'Test body content',
                    'tags' => [['name' => 'PHP'], ['name' => 'Laravel']],
                    'likes_count' => 10,
                    'created_at' => '2026-01-15T00:00:00+09:00',
                    'url' => 'https://qiita.com/user/items/test123',
                ]);

            $geminiService = Mockery::mock(GeminiService::class);
            $geminiService->shouldReceive('summarize')
                ->once()
                ->andReturn('Test summary');

            $this->app->instance(QiitaService::class, $qiitaService);
            $this->app->instance(GeminiService::class, $geminiService);

            $response = $this->actingAs($this->user)
                ->post(route('missions.blog-url.submit'), [
                    'url' => 'https://qiita.com/user/items/test123',
                ]);

            $response->assertStatus(200);
            $response->assertViewIs('missions.blog-preview');

            // Qiita記事が保存されているか確認
            $this->assertDatabaseHas('qiita_articles', [
                'user_id' => $this->user->id,
                'item_id' => 'test123',
                'title' => 'Test Article',
            ]);

            // マイルが付与されているか確認
            $this->assertDatabaseHas('mile_histories', [
                'user_id' => $this->user->id,
                'mission_id' => $this->mission->id,
                'type' => 'earn',
            ]);
        });

        it('validates URL is required', function () {
            $response = $this->actingAs($this->user)
                ->post(route('missions.blog-url.submit'), []);

            $response->assertSessionHasErrors('url');
        });

        it('handles Qiita API failure gracefully', function () {
            $qiitaService = Mockery::mock(QiitaService::class);
            $qiitaService->shouldReceive('fetchItemFromUrl')
                ->once()
                ->andReturn(null);

            $this->app->instance(QiitaService::class, $qiitaService);

            $response = $this->actingAs($this->user)
                ->post(route('missions.blog-url.submit'), [
                    'url' => 'https://qiita.com/invalid/url',
                ]);

            $response->assertRedirect();
            $response->assertSessionHasErrors('url');
        });
    });

    describe('show', function () {
        it('redirects to blog URL form for write_tech_blog mission', function () {
            $mission = Mission::factory()->writeTechBlog()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.show', $mission));

            $response->assertRedirect(route('missions.blog-url.form'));
        });

        it('redirects to form for event_speaker mission', function () {
            $mission = Mission::factory()->eventSpeaker()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.show', $mission));

            $response->assertRedirect(route('missions.form.create', ['mission' => $mission]));
        });

        it('redirects to form for event_organizer mission', function () {
            $mission = Mission::factory()->eventOrganizer()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.show', $mission));

            $response->assertRedirect(route('missions.form.create', ['mission' => $mission]));
        });

        it('redirects to form for acquire_certificate mission', function () {
            $mission = Mission::factory()->acquireCertificate()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.show', $mission));

            $response->assertRedirect(route('missions.form.create', ['mission' => $mission]));
        });
    });
});
