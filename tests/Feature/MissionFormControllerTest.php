<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Mission;
use App\Models\MissionForm;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Mission Form Controller', function () {
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

        $this->mission = Mission::factory()->eventSpeaker()->create([
            'company_id' => null,
            'user_id' => null,
        ]);
    });

    describe('create', function () {
        it('displays mission form', function () {
            $response = $this->actingAs($this->user)
                ->get(route('missions.form.create', ['mission' => $this->mission]));

            $response->assertStatus(200);
            $response->assertViewIs('missions.form');
            $response->assertViewHas('mission', $this->mission);
        });
    });

    describe('store', function () {
        it('submits mission form and awards miles', function () {
            $formData = [
                'title' => 'Laravel Conference 2026',
                'occurred_on' => '2026-01-15',
                'details' => 'Gave a talk about Laravel best practices',
                'evidence_url' => 'https://example.com/conference',
            ];

            $response = $this->actingAs($this->user)
                ->post(route('missions.form.store', ['mission' => $this->mission]), $formData);

            $response->assertRedirect(route('missions.index'));
            $response->assertSessionHas('achievementData');

            // MissionFormが保存されているか確認
            $this->assertDatabaseHas('mission_forms', [
                'user_id' => $this->user->id,
                'mission_id' => $this->mission->id,
                'title' => 'Laravel Conference 2026',
                'category' => 'event_speaker',
            ]);

            // UserMissionが作成され、完了しているか確認
            $this->assertDatabaseHas('user_missions', [
                'user_id' => $this->user->id,
                'mission_id' => $this->mission->id,
            ]);

            // マイルが付与されているか確認
            $this->assertDatabaseHas('mile_histories', [
                'user_id' => $this->user->id,
                'mission_id' => $this->mission->id,
                'type' => 'earn',
            ]);

            // タイムラインイベントが作成されているか確認
            $this->assertDatabaseHas('timeline_events', [
                'user_id' => $this->user->id,
                'event_type' => 'event_speaking',
            ]);
        });

        it('validates required fields', function () {
            $response = $this->actingAs($this->user)
                ->post(route('missions.form.store', ['mission' => $this->mission]), []);

            $response->assertSessionHasErrors(['title', 'occurred_on']);
        });

        it('validates occurred_on is a valid date', function () {
            $response = $this->actingAs($this->user)
                ->post(route('missions.form.store', ['mission' => $this->mission]), [
                    'title' => 'Test',
                    'occurred_on' => 'invalid-date',
                ]);

            $response->assertSessionHasErrors('occurred_on');
        });

        it('validates evidence_url is a valid URL when provided', function () {
            $response = $this->actingAs($this->user)
                ->post(route('missions.form.store', ['mission' => $this->mission]), [
                    'title' => 'Test',
                    'occurred_on' => '2026-01-15',
                    'evidence_url' => 'not-a-url',
                ]);

            $response->assertSessionHasErrors('evidence_url');
        });

        it('accepts optional fields as null', function () {
            $response = $this->actingAs($this->user)
                ->post(route('missions.form.store', ['mission' => $this->mission]), [
                    'title' => 'Minimal Submission',
                    'occurred_on' => '2026-01-15',
                ]);

            $response->assertRedirect(route('missions.index'));
            $response->assertSessionHasNoErrors();

            $this->assertDatabaseHas('mission_forms', [
                'user_id' => $this->user->id,
                'title' => 'Minimal Submission',
                'details' => null,
                'evidence_url' => null,
            ]);
        });
    });
});
