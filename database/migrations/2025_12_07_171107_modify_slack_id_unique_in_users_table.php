<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slack_id']); // UNIQUE 削除
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('slack_id'); // 元に戻せるように
        });
    }
};
