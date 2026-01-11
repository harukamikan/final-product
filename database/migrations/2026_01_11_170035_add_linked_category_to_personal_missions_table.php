<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_missions', function (Blueprint $table) {
            $table->string('linked_category')->nullable()->after('key');
            // 'write_tech_blog', 'acquire_certificate', 'event_speaker', 'event_organizer'
        });
    }

    public function down(): void
    {
        Schema::table('personal_missions', function (Blueprint $table) {
            $table->dropColumn('linked_category');
        });
    }
};