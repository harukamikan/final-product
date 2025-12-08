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
    Schema::create('mile_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('mission_id')->nullable()->constrained()->nullOnDelete();
        $table->integer('miles');             // +50 とか
        $table->string('type')->default('earn'); // 将来「消費」も入れたくなったとき用
        $table->string('description')->nullable();
        $table->timestamps();
    Schema::table('users', function (Blueprint $table) {
        $table->integer('total_miles')->default(0);
});

});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
