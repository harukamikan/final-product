<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->string('rank', 20)->change();
        });
    }

    public function down()
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->string('rank', 1)->change(); // 元が char(1) 等なら合わせる
        });
    }
};

