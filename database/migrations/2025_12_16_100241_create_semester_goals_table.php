<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category'); // ブログ、資格、登壇など
            $table->text('title'); // 目標内容
            $table->date('deadline')->nullable(); // 期限
            $table->string('semester')->nullable(); // 学期（例: 2025年前期）
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_goals');
    }
};