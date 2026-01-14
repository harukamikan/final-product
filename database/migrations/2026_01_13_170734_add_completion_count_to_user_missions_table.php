<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_missions', function (Blueprint $table) {
            $table->unsignedInteger('completion_count')->default(0)->after('progress_count');
        });
    }

    public function down(): void
    {
        Schema::table('user_missions', function (Blueprint $table) {
            $table->dropColumn('completion_count');
        });
    }
};