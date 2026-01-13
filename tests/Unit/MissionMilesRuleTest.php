<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Rules\MissionMilesRule;
use App\Services\MissionLimitsService;

class MissionMilesRuleTest extends TestCase
{
    /** @test */
    public function it_passes_for_miles_within_range()
    {
        $rule = new MissionMilesRule('write_tech_blog');
        $this->assertTrue($rule->passes('reward_miles', 100));
        $this->assertTrue($rule->passes('reward_miles', 50));  // min
        $this->assertTrue($rule->passes('reward_miles', 300)); // max
    }

    /** @test */
    public function it_fails_for_miles_below_minimum()
    {
        $rule = new MissionMilesRule('write_tech_blog');
        $this->assertFalse($rule->passes('reward_miles', 49));
        $this->assertFalse($rule->passes('reward_miles', 0));
        $this->assertFalse($rule->passes('reward_miles', 30));
    }

    /** @test */
    public function it_fails_for_miles_above_maximum()
    {
        $rule = new MissionMilesRule('write_tech_blog');
        $this->assertFalse($rule->passes('reward_miles', 301));
        $this->assertFalse($rule->passes('reward_miles', 500));
        $this->assertFalse($rule->passes('reward_miles', 1000));
    }

    /** @test */
    public function it_returns_appropriate_error_message()
    {
        $rule = new MissionMilesRule('event_speaker');
        $rule->passes('reward_miles', 50); // trigger validation
        
        $message = $rule->message();
        
        $this->assertStringContainsString('80', $message);  // min
        $this->assertStringContainsString('400', $message); // max
    }

    /** @test */
    public function it_works_for_different_mission_keys()
    {
        // write_tech_blog: 50-300
        $rule1 = new MissionMilesRule('write_tech_blog');
        $this->assertTrue($rule1->passes('reward_miles', 200));
        $this->assertFalse($rule1->passes('reward_miles', 400));

        // event_organizer: 100-500
        $rule2 = new MissionMilesRule('event_organizer');
        $this->assertTrue($rule2->passes('reward_miles', 200));
        $this->assertTrue($rule2->passes('reward_miles', 400));
        $this->assertFalse($rule2->passes('reward_miles', 50));

        // acquire_certificate: 30-600
        $rule3 = new MissionMilesRule('acquire_certificate');
        $this->assertTrue($rule3->passes('reward_miles', 30));
        $this->assertTrue($rule3->passes('reward_miles', 600));
        $this->assertFalse($rule3->passes('reward_miles', 20));
    }
}
