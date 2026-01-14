<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->date('personal_mission_edit_start')->nullable()->after('email');
            $table->date('personal_mission_edit_end')->nullable()->after('personal_mission_edit_start');
        });
    }

    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->dropColumn(['personal_mission_edit_start', 'personal_mission_edit_end']);
        });
    }
};