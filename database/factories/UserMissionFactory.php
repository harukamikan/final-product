<?php

namespace Database\Factories;

use App\Models\UserMission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserMission>
 */
class UserMissionFactory extends Factory
{
    protected $model = UserMission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'company_id' => null,
            'mission_id' => null,
            'proof_url' => null,
            'progress_count' => 0,
            'completion_count' => 0,
            'completed_at' => null,
            'related_personal_mission_id' => null,
        ];
    }

    /**
     * ミッション完了状態
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'progress_count' => 1,
            'completion_count' => 1,
            'completed_at' => now(),
        ]);
    }

    /**
     * 進行中状態（未完了）
     */
    public function inProgress(int $progressCount = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'progress_count' => $progressCount,
            'completion_count' => 0,
            'completed_at' => null,
        ]);
    }

    /**
     * 証明URLあり
     */
    public function withProofUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'proof_url' => fake()->url(),
        ]);
    }
}
