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
    Schema::create('user_missions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
        $table->unsignedInteger('progress_count')->default(0);
        $table->string('proof_url')->nullable();  // QiitaのURLなど
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
        $table->unique(['user_id', 'mission_id']); // 同じミッションを1行で管理
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
