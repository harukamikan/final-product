<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_missions', function (Blueprint $table) {
            $table->unsignedInteger('progress_count')->default(0)->after('required_count');
            $table->timestamp('completed_at')->nullable()->after('progress_count');
        });
    }

    public function down(): void
    {
        Schema::table('personal_missions', function (Blueprint $table) {
            $table->dropColumn(['progress_count', 'completed_at']);
        });
    }
};