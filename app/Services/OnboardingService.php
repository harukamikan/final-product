<?php

namespace App\Services;

use App\Models\User;
use App\Models\Mission;
use App\Models\OnboardingSurvey;
use Illuminate\Support\Facades\DB;

class OnboardingService
{
    /**
     * Determine user segment based on Q1 (role - engineer job type)
     */
    public function determineSegment(array $surveyData): string
    {
        return match ($surveyData['role']) {
            'dev' => 'engineer_dev',
            'infra' => 'engineer_infra',
            'mgmt' => 'engineer_mgmt',
            'all' => 'engineer_fullstack',
            default => 'engineer_dev', // fallback
        };
    }

    /**
     * Generate 3 initial missions for the user based on segment and modifiers
     * Returns array of created Mission objects
     */
    public function generateInitialMissions(User $user, string $segment, array $surveyData): array
    {
        $missions = [];
        
        // Determine primary mission type from segment
        $primaryType = $this->segmentToMissionType($segment);
        
        // Get compatible secondary missions
        $secondaryTypes = $this->getCompatibleMissionTypes($primaryType);
        
        // Prepare modifiers from Q1/Q3/Q4 - include role and experience level for difficulty calculation
        $modifiers = [
            'is_busy' => $surveyData['current_situation'] === 'busy',
            'is_new' => $surveyData['current_situation'] === 'new',
            'is_senior' => $surveyData['experience_level'] === 'senior',
            'is_junior' => $surveyData['experience_level'] === 'junior',
            'role' => $surveyData['role'],
            'experience_level' => $surveyData['experience_level'],
        ];

        DB::transaction(function () use ($user, $primaryType, $secondaryTypes, $modifiers, &$missions) {
            // Create primary mission
            $missions[] = $this->createMission($user, $primaryType, $modifiers, isPrimary: true);
            
            // Create 2 secondary missions
            foreach (array_slice($secondaryTypes, 0, 2) as $type) {
                $missions[] = $this->createMission($user, $type, $modifiers, isPrimary: false);
            }
        });

        return $missions;
    }

    /**
     * Convert segment to mission type
     */
    protected function segmentToMissionType(string $segment): string
    {
        return match ($segment) {
            'output_blog' => 'write_tech_blog',
            'community_event' => 'event_organizer',
            'speaker' => 'event_speaker',
            'skillup_cert' => 'acquire_certificate',
            
            // Engineer job type segments
            'engineer_dev' => 'write_tech_blog',
            'engineer_infra' => 'acquire_certificate',
            'engineer_mgmt' => 'event_organizer',
            'engineer_fullstack' => 'write_tech_blog',
            
            default => 'write_tech_blog',
        };
    }

    /**
     * Get compatible mission types for the given primary type
     */
    protected function getCompatibleMissionTypes(string $primaryType): array
    {
        return match ($primaryType) {
            'write_tech_blog' => ['event_speaker', 'acquire_certificate'],
            'event_organizer' => ['write_tech_blog', 'event_speaker'],
            'event_speaker' => ['write_tech_blog', 'event_organizer'],
            'acquire_certificate' => ['write_tech_blog', 'event_speaker'],
            default => ['write_tech_blog', 'event_speaker'],
        };
    }

    /**
     * Create a single mission with modifiers applied
     */
    protected function createMission(User $user, string $missionType, array $modifiers, bool $isPrimary): Mission
    {
        // Pass the full survey data to getMissionTemplate for calculation
        $template = $this->getMissionTemplate($missionType, $modifiers, $isPrimary);
        
        return Mission::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'key' => $template['key'],
            'title' => $template['title'],
            'description' => $template['description'],
            'trigger_type' => $template['trigger_type'],
            'required_count' => $template['required_count'],
            'reward_miles' => $template['reward_miles'],
            'mile_min' => $template['mile_min'],
            'mile_max' => $template['mile_max'],
            'repeatable' => false, // Initial missions are one-time
        ]);
    }

    /**
     * Calculate required count based on experience level and role
     */
    protected function calculateRequiredCount(array $modifiers): int
    {
        // Extract experience level and role from modifiers
        $experienceLevel = $modifiers['experience_level'] ?? 'mid';
        $role = $modifiers['role'] ?? 'IC';
        
        // Base count by experience level
        $baseCount = match ($experienceLevel) {
            'junior' => 1,
            'mid' => 2,
            'senior' => 2,
            default => 1,
        };
        
        // Add modifier for senior TechLead/EM
        $roleModifier = 0;
        if ($experienceLevel === 'senior' && in_array($role, ['TechLead', 'EM'])) {
            $roleModifier = 1;
        }
        
        return $baseCount + $roleModifier;
    }

    /**
     * Get mission template with adjusted difficulty/wording based on modifiers
     */
    protected function getMissionTemplate(string $missionType, array $modifiers, bool $isPrimary): array
    {
        // Calculate required count based on user profile
        $requiredCount = $this->calculateRequiredCount($modifiers);
        
        // Base templates with dynamic count and miles within defined ranges
        $templates = [
            'write_tech_blog' => [
                'key' => 'write_tech_blog',
                'trigger_type' => 'tech_blog_posted',
                'title' => '技術ブログを書こう',
                'description' => "Qiitaなどで技術記事を{$requiredCount}本投稿しましょう",
                'required_count' => $requiredCount,
                'reward_miles' => 45 * $requiredCount, // Mid-range: 30-60
                'mile_min' => 30,
                'mile_max' => 60,
            ],
            'event_organizer' => [
                'key' => 'event_organizer',
                'trigger_type' => 'google_form_submitted',
                'title' => 'イベントを企画しよう',
                'description' => "社内外の勉強会やイベントを{$requiredCount}回企画・開催しましょう",
                'required_count' => $requiredCount,
                'reward_miles' => 95 * $requiredCount, // Mid-range: 70-120
                'mile_min' => 70,
                'mile_max' => 120,
            ],
            'event_speaker' => [
                'key' => 'event_speaker',
                'trigger_type' => 'google_form_submitted',
                'title' => 'イベントで登壇しよう',
                'description' => "勉強会やカンファレンスで{$requiredCount}回発表しましょう",
                'required_count' => $requiredCount,
                'reward_miles' => 80 * $requiredCount, // Mid-range: 60-100
                'mile_min' => 60,
                'mile_max' => 100,
            ],
            'acquire_certificate' => [
                'key' => 'acquire_certificate',
                'trigger_type' => 'google_form_submitted',
                'title' => '資格を取得しよう',
                'description' => "技術系の資格を{$requiredCount}つ取得しましょう",
                'required_count' => $requiredCount,
                'reward_miles' => 115 * $requiredCount, // Mid-range: 80-150
                'mile_min' => 80,
                'mile_max' => 150,
            ],
        ];

        $template = $templates[$missionType] ?? $templates['write_tech_blog'];

        // Apply modifiers for wording adjustments
        if ($modifiers['is_busy'] || $modifiers['is_new']) {
            // Softer wording for busy/new users
            if ($missionType === 'write_tech_blog') {
                $template['description'] = $requiredCount === 1 
                    ? '短い記事でOK！まずは1本書いてみましょう'
                    : "短い記事でOK！まずは{$requiredCount}本書いてみましょう";
            } elseif ($missionType === 'event_organizer') {
                $template['description'] = $requiredCount === 1
                    ? '小規模でOK！社内LTなどから始めてみましょう'
                    : "小規模でOK！{$requiredCount}回開催してみましょう";
            } elseif ($missionType === 'event_speaker') {
                $template['description'] = $requiredCount === 1
                    ? '5分のLTでもOK！まずは話してみましょう'
                    : "5分のLTでもOK！{$requiredCount}回話してみましょう";
            } elseif ($missionType === 'acquire_certificate') {
                $template['description'] = $requiredCount === 1
                    ? '興味のある分野から挑戦してみましょう'
                    : "興味のある分野から{$requiredCount}つ挑戦してみましょう";
            }

            
            // Primary mission gets extra encouragement
            if ($isPrimary) {
                $template['description'] = '【最初のチャレンジ】' . $template['description'];
            }
        }

        if ($modifiers['is_senior']) {
            // Senior engineers: use upper range of miles instead of multiplier
            if ($missionType === 'write_tech_blog') {
                $template['reward_miles'] = 55 * $requiredCount; // Higher within 30-60 range
            } elseif ($missionType === 'event_speaker') {
                $template['reward_miles'] = 90 * $requiredCount; // Higher within 60-100 range
            } elseif ($missionType === 'event_organizer') {
                $template['reward_miles'] = 110 * $requiredCount; // Higher within 70-120 range
            } elseif ($missionType === 'acquire_certificate') {
                $template['reward_miles'] = 140 * $requiredCount; // Higher within 80-150 range
            }
            
            if ($isPrimary) {
                if ($missionType === 'write_tech_blog') {
                    $template['description'] = $requiredCount === 1
                        ? '他のエンジニアの参考になる技術記事を書きましょう'
                        : "他のエンジニアの参考になる技術記事を{$requiredCount}本書きましょう";
                } elseif ($missionType === 'event_organizer') {
                    $template['description'] = $requiredCount === 1
                        ? 'コミュニティを盛り上げるイベントを企画しましょう'
                        : "コミュニティを盛り上げるイベントを{$requiredCount}回企画しましょう";
                } elseif ($missionType === 'event_speaker') {
                    $template['description'] = $requiredCount === 1
                        ? '知見を共有するセッションで登壇しましょう'
                        : "知見を共有するセッションで{$requiredCount}回登壇しましょう";
                } elseif ($missionType === 'acquire_certificate') {
                    $template['description'] = $requiredCount === 1
                        ? '高度な技術資格に挑戦しましょう'
                        : "高度な技術資格を{$requiredCount}つ取得しましょう";
                }
            }
        }

        return $template;
    }

    /**
     * Save survey response and return OnboardingSurvey model
     */
    public function saveSurveyResponse(User $user, array $surveyData): OnboardingSurvey
    {
        $segment = $this->determineSegment($surveyData);
        
        return OnboardingSurvey::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'role' => $surveyData['role'],
            'preferred_output' => $surveyData['preferred_output'],
            'current_situation' => $surveyData['current_situation'],
            'experience_level' => $surveyData['experience_level'],
            'segment' => $segment,
        ]);
    }
}
