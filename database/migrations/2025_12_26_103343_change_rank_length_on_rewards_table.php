<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // rewardsテーブルが存在する場合のみ変更
        if (Schema::hasTable('rewards')) {
            Schema::table('rewards', function (Blueprint $table) {
                $table->string('rank', 20)->change();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('rewards')) {
            Schema::table('rewards', function (Blueprint $table) {
                $table->string('rank', 1)->change();
            });
        }
    }
};