<?php

namespace App\Rules;

use App\Services\MissionLimitsService;
use Illuminate\Contracts\Validation\Rule;

class MissionMilesRule implements Rule
{
    protected string $missionKey;
    protected MissionLimitsService $limitsService;

    /**
     * Create a new rule instance.
     *
     * @param string $missionKey
     */
    public function __construct(string $missionKey)
    {
        $this->missionKey = $missionKey;
        $this->limitsService = app(MissionLimitsService::class);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return $this->limitsService->isWithinRange($this->missionKey, (int)$value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        $limits = $this->limitsService->getLimits($this->missionKey);
        return "報酬マイルは {$limits['min_miles']} 〜 {$limits['max_miles']} の範囲で設定してください。";
    }
}
