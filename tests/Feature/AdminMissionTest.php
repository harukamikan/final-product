<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Mission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Admin Mission CRUD', function () {
    beforeEach(function () {
        // テスト用の会社と管理者を作成
        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);
        
        $this->admin = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
    });

    describe('Index', function () {
        it('displays missions list for admin', function () {
            // 企業ミッション（user_id = null）を作成
            $missions = Mission::factory()
                ->count(3)
                ->create(['company_id' => null, 'user_id' => null]);

            // 個人ミッション（user_id あり）を作成 - これは表示されないはず
            Mission::factory()->personal($this->admin->id)->create();

            $response = $this->actingAs($this->admin)
                ->get(route('admin.missions.index'));

            $response->assertStatus(200);
            $response->assertViewIs('admin.missions.index');
            $response->assertViewHas('missions');

            // ページネーションされたデータを取得
            $viewMissions = $response->viewData('missions');
            expect($viewMissions->total())->toBe(3);
        });

        it('paginates missions correctly', function () {
            // 25件のミッションを作成（ページサイズは20）
            Mission::factory()
                ->count(25)
                ->create(['company_id' => null, 'user_id' => null]);

            $response = $this->actingAs($this->admin)
                ->get(route('admin.missions.index'));

            $response->assertStatus(200);
            $viewMissions = $response->viewData('missions');
            
            expect($viewMissions->count())->toBe(20);
            expect($viewMissions->total())->toBe(25);
        });
    });

    describe('Create', function () {
        it('displays create form', function () {
            $response = $this->actingAs($this->admin)
                ->get(route('admin.missions.create'));

            $response->assertStatus(200);
            $response->assertViewIs('admin.missions.create');
        });

        it('creates a new mission with valid data', function () {
            $missionData = [
                'mission_type' => 'write_tech_blog',
                'title' => 'Test Tech Blog Mission',
                'description' => 'Test description',
                'required_count' => 1,
                'reward_miles' => 60,
                'repeatable' => false,
            ];

            $response = $this->actingAs($this->admin)
                ->post(route('admin.missions.store'), $missionData);

            $response->assertRedirect(route('admin.missions.index'));
            $response->assertSessionHas('success');

            $this->assertDatabaseHas('missions', [
                'key' => 'write_tech_blog',
                'title' => 'Test Tech Blog Mission',
                'trigger_type' => 'tech_blog_posted',
                'reward_miles' => 60,
            ]);
        });

        it('validates required fields', function () {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.missions.store'), []);

            $response->assertSessionHasErrors(['mission_type', 'title', 'required_count', 'reward_miles', 'repeatable']);
        });

        it('validates reward_miles range for write_tech_blog', function () {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.missions.store'), [
                    'mission_type' => 'write_tech_blog',
                    'title' => 'Test',
                    'required_count' => 1,
                    'reward_miles' => 40, // min is 50
                    'repeatable' => false,
                ]);

            $response->assertSessionHasErrors('reward_miles');
        });

        it('validates reward_miles range for event_speaker', function () {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.missions.store'), [
                    'mission_type' => 'event_speaker',
                    'title' => 'Test',
                    'required_count' => 1,
                    'reward_miles' => 150, // max is 100
                    'repeatable' => false,
                ]);

            $response->assertSessionHasErrors('reward_miles');
        });

        it('accepts reward_miles at exact minimum', function () {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.missions.store'), [
                    'mission_type' => 'event_organizer',
                    'title' => 'Test Event',
                    'required_count' => 1,
                    'reward_miles' => 80, // exact min
                    'repeatable' => false,
                ]);

            $response->assertRedirect(route('admin.missions.index'));
            $response->assertSessionHasNoErrors();
        });

        it('accepts reward_miles at exact maximum', function () {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.missions.store'), [
                    'mission_type' => 'acquire_certificate',
                    'title' => 'Test Certificate',
                    'required_count' => 1,
                    'reward_miles' => 60, // exact max
                    'repeatable' => false,
                ]);

            $response->assertRedirect(route('admin.missions.index'));
            $response->assertSessionHasNoErrors();
        });
    });

    describe('Update', function () {
        it('displays edit form', function () {
            $mission = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            $response = $this->actingAs($this->admin)
                ->get(route('admin.missions.edit', $mission));

            $response->assertStatus(200);
            $response->assertViewIs('admin.missions.edit');
            $response->assertViewHas('mission', $mission);
        });

        it('updates mission with valid data', function () {
            $mission = Mission::factory()->writeTechBlog()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            $updatedData = [
                'mission_type' => 'write_tech_blog',
                'title' => 'Updated Tech Blog Mission',
                'description' => 'Updated description',
                'required_count' => 2,
                'reward_miles' => 70,
                'repeatable' => true,
            ];

            $response = $this->actingAs($this->admin)
                ->put(route('admin.missions.update', $mission), $updatedData);

            $response->assertRedirect(route('admin.missions.index'));
            $response->assertSessionHas('success');

            $this->assertDatabaseHas('missions', [
                'id' => $mission->id,
                'title' => 'Updated Tech Blog Mission',
                'reward_miles' => 70,
                'repeatable' => true,
            ]);
        });

        it('validates reward_miles when updating', function () {
            $mission = Mission::factory()->eventSpeaker()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            $response = $this->actingAs($this->admin)
                ->put(route('admin.missions.update', $mission), [
                    'mission_type' => 'event_speaker',
                    'title' => 'Test',
                    'required_count' => 1,
                    'reward_miles' => 200, // over max
                    'repeatable' => false,
                ]);

            $response->assertSessionHasErrors('reward_miles');
        });
    });

    describe('Delete', function () {
        it('deletes a mission', function () {
            $mission = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            $response = $this->actingAs($this->admin)
                ->delete(route('admin.missions.destroy', $mission));

            $response->assertRedirect(route('admin.missions.index'));
            $response->assertSessionHas('success');

            $this->assertDatabaseMissing('missions', [
                'id' => $mission->id,
            ]);
        });
    });
});
