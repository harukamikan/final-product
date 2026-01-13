<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MissionMilesValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト用の会社と管理者を作成
        $this->company = Company::factory()->create();
        $this->admin = User::factory()->create([
            'company_id' => $this->company->id,
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function admin_cannot_create_mission_with_miles_below_minimum()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.missions.store'), [
            'mission_type' => 'write_tech_blog',
            'title' => 'Test Tech Blog Mission',
            'description' => 'Test description',
            'required_count' => 1,
            'reward_miles' => 30, // min is 50
            'repeatable' => false,
        ]);

        $response->assertSessionHasErrors('reward_miles');
        $this->assertStringContainsString('50', session('errors')->first('reward_miles'));
    }

    /** @test */
    public function admin_cannot_create_mission_with_miles_above_maximum()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.missions.store'), [
            'mission_type' => 'write_tech_blog',
            'title' => 'Test Tech Blog Mission',
            'description' => 'Test description',
            'required_count' => 1,
            'reward_miles' => 500, // max is 300
            'repeatable' => false,
        ]);

        $response->assertSessionHasErrors('reward_miles');
        $this->assertStringContainsString('300', session('errors')->first('reward_miles'));
    }

    /** @test */
    public function admin_can_create_mission_with_miles_within_range()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.missions.store'), [
            'mission_type' => 'write_tech_blog',
            'title' => 'Test Tech Blog Mission',
            'description' => 'Test description',
            'required_count' => 1,
            'reward_miles' => 100, // within 50-300
            'repeatable' => false,
        ]);

        $response->assertRedirect(route('admin.missions.index'));
        $response->assertSessionHasNoErrors();
        
        $this->assertDatabaseHas('missions', [
            'key' => 'write_tech_blog',
            'title' => 'Test Tech Blog Mission',
            'reward_miles' => 100,
        ]);
    }

    /** @test */
    public function admin_can_create_mission_at_exact_minimum()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.missions.store'), [
            'mission_type' => 'event_speaker',
            'title' => 'Test Event Speaker Mission',
            'required_count' => 1,
            'reward_miles' => 80, // exact min
            'repeatable' => false,
        ]);

        $response->assertRedirect(route('admin.missions.index'));
        $this->assertDatabaseHas('missions', [
            'key' => 'event_speaker',
            'reward_miles' => 80,
        ]);
    }

    /** @test */
    public function admin_can_create_mission_at_exact_maximum()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.missions.store'), [
            'mission_type' => 'acquire_certificate',
            'title' => 'Test Certificate Mission',
            'required_count' => 1,
            'reward_miles' => 600, // exact max
            'repeatable' => false,
        ]);

        $response->assertRedirect(route('admin.missions.index'));
        $this->assertDatabaseHas('missions', [
            'key' => 'acquire_certificate',
            'reward_miles' => 600,
        ]);
    }

    /** @test */
    public function different_mission_types_have_different_limits()
    {
        // event_organizer は min=100, max=500
        $response1 = $this->actingAs($this->admin)->post(route('admin.missions.store'), [
            'mission_type' => 'event_organizer',
            'title' => 'Test Event Organizer Mission',
            'required_count' => 1,
            'reward_miles' => 90, // below 100
            'repeatable' => false,
        ]);

        $response1->assertSessionHasErrors('reward_miles');

        // 範囲内なら成功
        $response2 = $this->actingAs($this->admin)->post(route('admin.missions.store'), [
            'mission_type' => 'event_organizer',
            'title' => 'Test Event Organizer Mission 2',
            'required_count' => 1,
            'reward_miles' => 150, // within range
            'repeatable' => false,
        ]);

        $response2->assertRedirect(route('admin.missions.index'));
    }
}
