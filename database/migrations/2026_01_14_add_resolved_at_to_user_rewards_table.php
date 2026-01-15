<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('user_rewards', 'resolved_at')) {
            Schema::table('user_rewards', function (Blueprint $table) {
                $table->timestamp('resolved_at')->nullable()->after('used_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('user_rewards', function (Blueprint $table) {
            $table->dropColumn('resolved_at');
        });
    }
};
