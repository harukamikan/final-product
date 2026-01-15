<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Mission;
use App\Models\UserMission;
use App\Models\PersonalMission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Mission List Controller', function () {
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
            'total_miles' => 100,
        ]);
    });

    describe('index', function () {
        it('displays incomplete missions only', function () {
            // 未完了ミッション
            $incompleteMission = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            // 完了済みミッション
            $completedMission = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            UserMission::factory()->completed()->create([
                'user_id' => $this->user->id,
                'mission_id' => $completedMission->id,
                'company_id' => $this->company->id,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.index'));

            $response->assertStatus(200);
            $response->assertViewIs('missions.user_index');
            $response->assertViewHas(['missions', 'totalMiles', 'earnedThisTime']);

            $viewMissions = $response->viewData('missions');
            
            // 未完了ミッションのみが含まれている
            expect($viewMissions->contains('id', $incompleteMission->id))->toBeTrue();
            expect($viewMissions->contains('id', $completedMission->id))->toBeFalse();
        });

        it('displays missions with no UserMission record', function () {
            // まだ一度も触ってないミッション
            $newMission = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.index'));

            $viewMissions = $response->viewData('missions');
            expect($viewMissions->contains('id', $newMission->id))->toBeTrue();
        });

        it('displays missions with progress but not completed', function () {
            $mission = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
                'required_count' => 3,
            ]);

            UserMission::factory()->inProgress(2)->create([
                'user_id' => $this->user->id,
                'mission_id' => $mission->id,
                'company_id' => $this->company->id,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.index'));

            $viewMissions = $response->viewData('missions');
            expect($viewMissions->contains('id', $mission->id))->toBeTrue();
        });

        it('passes total miles to view', function () {
            $response = $this->actingAs($this->user)
                ->get(route('missions.index'));

            $totalMiles = $response->viewData('totalMiles');
            expect($totalMiles)->toBe(100);
        });

        it('passes earned miles from session', function () {
            session(['earned_miles' => 50]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.index'));

            $earnedThisTime = $response->viewData('earnedThisTime');
            expect($earnedThisTime)->toBe(50);
        });
    });

    describe('personal', function () {
        it('displays personal missions', function () {
            // 個人ミッションを作成
            $personalMission = PersonalMission::create([
                'user_id' => $this->user->id,
                'company_id' => $this->company->id,
                'key' => 'personal_test_goal',
                'title' => 'Personal Goal',
                'description' => 'My personal goal',
                'required_count' => 5,
                'progress_count' => 2,
                'linked_category' => 'write_tech_blog',
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.personal'));

            $response->assertStatus(200);
            $response->assertViewIs('missions.personal');
            $response->assertViewHas(['missions', 'totalMiles', 'earnedThisTime']);

            $viewMissions = $response->viewData('missions');
            expect($viewMissions->contains('id', $personalMission->id))->toBeTrue();
        });

        it('only displays incomplete personal missions', function () {
            // 未完了
            $incompleteMission = PersonalMission::create([
                'user_id' => $this->user->id,
                'company_id' => $this->company->id,
                'key' => 'personal_incomplete',
                'title' => 'Incomplete',
                'required_count' => 5,
                'progress_count' => 2,
            ]);

            // 完了済み
            $completedMission = PersonalMission::create([
                'user_id' => $this->user->id,
                'company_id' => $this->company->id,
                'key' => 'personal_completed',
                'title' => 'Completed',
                'required_count' => 5,
                'progress_count' => 5,
                'completed_at' => now(),
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.personal'));

            $viewMissions = $response->viewData('missions');
            
            expect($viewMissions->contains('id', $incompleteMission->id))->toBeTrue();
            expect($viewMissions->contains('id', $completedMission->id))->toBeFalse();
        });
    });

    describe('completed', function () {
        it('displays completed company missions', function () {
            $mission = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            UserMission::factory()->completed()->create([
                'user_id' => $this->user->id,
                'mission_id' => $mission->id,
                'company_id' => $this->company->id,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.completed'));

            $response->assertStatus(200);
            $response->assertViewIs('missions.completed');
            $response->assertViewHas(['missions', 'personalMissions', 'totalMiles']);

            $viewMissions = $response->viewData('missions');
            expect($viewMissions->contains('id', $mission->id))->toBeTrue();
        });

        it('displays completed personal missions', function () {
            $personalMission = PersonalMission::create([
                'user_id' => $this->user->id,
                'company_id' => $this->company->id,
                'key' => 'personal_completed_test',
                'title' => 'Completed Personal',
                'required_count' => 5,
                'progress_count' => 5,
                'completed_at' => now(),
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.completed'));

            $viewPersonalMissions = $response->viewData('personalMissions');
            expect($viewPersonalMissions->contains('id', $personalMission->id))->toBeTrue();
        });

        it('filters by keyword', function () {
            $mission1 = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
                'title' => 'Laravel Tutorial',
            ]);

            $mission2 = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
                'title' => 'React Conference',
            ]);

            UserMission::factory()->completed()->create([
                'user_id' => $this->user->id,
                'mission_id' => $mission1->id,
                'company_id' => $this->company->id,
            ]);

            UserMission::factory()->completed()->create([
                'user_id' => $this->user->id,
                'mission_id' => $mission2->id,
                'company_id' => $this->company->id,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.completed', ['keyword' => 'Laravel']));

            $viewMissions = $response->viewData('missions');
            
            expect($viewMissions->contains('id', $mission1->id))->toBeTrue();
            expect($viewMissions->contains('id', $mission2->id))->toBeFalse();
        });

        it('filters by from_date', function () {
            $mission = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            UserMission::factory()->create([
                'user_id' => $this->user->id,
                'mission_id' => $mission->id,
                'company_id' => $this->company->id,
                'completed_at' => '2026-01-10',
                'progress_count' => 1,
                'completion_count' => 1,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.completed', ['from_date' => '2026-01-15']));

            $viewMissions = $response->viewData('missions');
            expect($viewMissions->isEmpty())->toBeTrue();

            $response2 = $this->actingAs($this->user)
                ->get(route('missions.completed', ['from_date' => '2026-01-05']));

            $viewMissions2 = $response2->viewData('missions');
            expect($viewMissions2->contains('id', $mission->id))->toBeTrue();
        });

        it('filters by to_date', function () {
            $mission = Mission::factory()->create([
                'company_id' => null,
                'user_id' => null,
            ]);

            UserMission::factory()->create([
                'user_id' => $this->user->id,
                'mission_id' => $mission->id,
                'company_id' => $this->company->id,
                'completed_at' => '2026-01-20',
                'progress_count' => 1,
                'completion_count' => 1,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('missions.completed', ['to_date' => '2026-01-15']));

            $viewMissions = $response->viewData('missions');
            expect($viewMissions->isEmpty())->toBeTrue();

            $response2 = $this->actingAs($this->user)
                ->get(route('missions.completed', ['to_date' => '2026-01-25']));

            $viewMissions2 = $response2->viewData('missions');
            expect($viewMissions2->contains('id', $mission->id))->toBeTrue();
        });
    });
});
