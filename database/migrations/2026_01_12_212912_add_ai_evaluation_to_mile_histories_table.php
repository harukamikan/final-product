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
        Schema::table('mile_histories', function (Blueprint $table) {
            $table->decimal('ai_score', 3, 2)->nullable()->after('description');
            $table->text('ai_reason')->nullable()->after('ai_score');
            $table->json('ai_raw_response')->nullable()->after('ai_reason');
            $table->timestamp('ai_evaluated_at')->nullable()->after('ai_raw_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mile_histories', function (Blueprint $table) {
            $table->dropColumn(['ai_score', 'ai_reason', 'ai_raw_response', 'ai_evaluated_at']);
        });
    }
};
