<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Mission;
use App\Models\UserMission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Mission Controller', function () {
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

        $this->mission = Mission::factory()->create([
            'company_id' => null,
            'user_id' => null,
            'trigger_type' => 'manual_complete',
        ]);
    });

    describe('complete', function () {
        it('completes a mission manually and awards miles', function () {
            $response = $this->actingAs($this->user)
                ->post(route('missions.complete', ['mission' => $this->mission]));

            $response->assertRedirect();
            $response->assertSessionHas('success');

            // UserMissionが作成され、完了しているか確認
            $this->assertDatabaseHas('user_missions', [
                'user_id' => $this->user->id,
                'mission_id' => $this->mission->id,
                'progress_count' => 1,
            ]);

            // マイルが付与されているか確認
            $this->assertDatabaseHas('mile_histories', [
                'user_id' => $this->user->id,
                'mission_id' => $this->mission->id,
                'type' => 'earn',
            ]);

            // ユーザーの合計マイルが更新されているか確認
            $this->user->refresh();
            expect($this->user->total_miles)->toBe($this->mission->reward_miles);
        });

        it('increments progress for repeatable missions', function () {
            $repeatableMission = Mission::factory()->repeatable()->create([
                'company_id' => null,
                'user_id' => null,
                'trigger_type' => 'manual_complete',
                'required_count' => 1,
            ]);

            // 1回目の完了
            $this->actingAs($this->user)
                ->post(route('missions.complete', ['mission' => $repeatableMission]));

            $userMission = UserMission::where('user_id', $this->user->id)
                ->where('mission_id', $repeatableMission->id)
                ->first();

            expect($userMission->completion_count)->toBe(1);
            expect($userMission->completed_at)->toBeNull(); // repeatableなのでリセットされる

            // 2回目の完了
            $this->actingAs($this->user)
                ->post(route('missions.complete', ['mission' => $repeatableMission]));

            $userMission->refresh();
            expect($userMission->completion_count)->toBe(2);

            // マイルが2回付与されているか確認
            $mileHistories = \App\Models\MileHistory::where('user_id', $this->user->id)
                ->where('mission_id', $repeatableMission->id)
                ->count();
            expect($mileHistories)->toBe(2);
        });

        it('does not award miles for already completed non-repeatable mission', function () {
            // 既に完了済みのミッションを作成
            $userMission = UserMission::factory()
                ->completed()
                ->create([
                    'user_id' => $this->user->id,
                    'mission_id' => $this->mission->id,
                    'company_id' => $this->company->id,
                ]);

            $initialMiles = $this->user->total_miles ?? 0;

            $response = $this->actingAs($this->user)
                ->post(route('missions.complete', ['mission' => $this->mission]));

            $response->assertRedirect();

            // マイルが追加されていないことを確認
            $this->user->refresh();
            expect($this->user->total_miles ?? 0)->toBe($initialMiles);

            // mile_historiesに新しいレコードが追加されていないことを確認
            $mileCount = \App\Models\MileHistory::where('user_id', $this->user->id)
                ->where('mission_id', $this->mission->id)
                ->count();
            expect($mileCount)->toBe(0);
        });
    });
});
