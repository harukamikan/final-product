<?php

namespace Database\Factories;

use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mission>
 */
class MissionFactory extends Factory
{
    protected $model = Mission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null, // 共有ミッション（企業ミッション）
            'company_id' => null,
            'key' => 'write_tech_blog',
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'trigger_type' => 'tech_blog_posted',
            'required_count' => 1,
            'reward_miles' => 60,
            'repeatable' => false,
        ];
    }

    /**
     * 技術ブログミッション
     */
    public function writeTechBlog(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'write_tech_blog',
            'title' => '技術ブログを書く',
            'trigger_type' => 'tech_blog_posted',
            'reward_miles' => fake()->numberBetween(50, 80),
        ]);
    }

    /**
     * イベント登壇ミッション
     */
    public function eventSpeaker(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'event_speaker',
            'title' => 'イベントで登壇する',
            'trigger_type' => 'google_form_submitted',
            'reward_miles' => fake()->numberBetween(70, 100),
        ]);
    }

    /**
     * イベント主催ミッション
     */
    public function eventOrganizer(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'event_organizer',
            'title' => 'イベントを主催する',
            'trigger_type' => 'google_form_submitted',
            'reward_miles' => fake()->numberBetween(80, 120),
        ]);
    }

    /**
     * 資格取得ミッション
     */
    public function acquireCertificate(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'acquire_certificate',
            'title' => '資格を取得する',
            'trigger_type' => 'google_form_submitted',
            'reward_miles' => fake()->numberBetween(30, 60),
        ]);
    }

    /**
     * 繰り返し可能なミッション
     */
    public function repeatable(): static
    {
        return $this->state(fn (array $attributes) => [
            'repeatable' => true,
        ]);
    }

    /**
     * 個人ミッション（特定ユーザー用）
     */
    public function personal(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }
}
