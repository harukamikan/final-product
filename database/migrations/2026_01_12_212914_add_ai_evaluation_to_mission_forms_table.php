<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mission_forms', function (Blueprint $table) {
            $table->decimal('ai_score', 3, 2)->nullable()->after('status');
            $table->text('ai_reason')->nullable()->after('ai_score');
            $table->json('ai_evaluation_signals')->nullable()->after('ai_reason');
            $table->unsignedInteger('calculated_miles')->nullable()->after('ai_evaluation_signals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mission_forms', function (Blueprint $table) {
            $table->dropColumn(['ai_score', 'ai_reason', 'ai_evaluation_signals', 'calculated_miles']);
        });
    }
};
