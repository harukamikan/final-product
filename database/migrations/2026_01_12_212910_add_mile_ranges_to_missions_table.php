<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Mission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->unsignedInteger('mile_min')->nullable()->after('reward_miles');
            $table->unsignedInteger('mile_max')->nullable()->after('mile_min');
        });

        // Backfill existing missions with ranges based on reward_miles
        // mile_min = reward_miles * 0.5 (rounded to nearest 10)
        // mile_max = reward_miles * 1.5 (rounded to nearest 10)
        $missions = Mission::all();
        foreach ($missions as $mission) {
            $rewardMiles = $mission->reward_miles ?? 0;
            
            $mileMin = round(($rewardMiles * 0.5) / 10) * 10;
            $mileMax = round(($rewardMiles * 1.5) / 10) * 10;
            
            // Ensure minimum values
            $mileMin = max(10, $mileMin);
            $mileMax = max(20, $mileMax);
            
            $mission->update([
                'mile_min' => $mileMin,
                'mile_max' => $mileMax,
            ]);
        }

        // Make columns non-nullable after backfill
        Schema::table('missions', function (Blueprint $table) {
            $table->unsignedInteger('mile_min')->nullable(false)->change();
            $table->unsignedInteger('mile_max')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['mile_min', 'mile_max']);
        });
    }
};
