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
    Schema::create('mission_forms', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('mission_id')->constrained()->cascadeOnDelete();

        $table->string('category');
        $table->string('title');
        $table->date('occurred_on');
        $table->text('details')->nullable();
        $table->string('evidence_url')->nullable();

        $table->string('status')->default('submitted');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_forms');
    }
};
