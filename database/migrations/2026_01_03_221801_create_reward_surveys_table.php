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
        Schema::create('reward_surveys', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete()
                ->name('reward_surveys_company_id_fk_new');

            $table->string('title');
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();

            $table->string('status')->default('active');
            // active / stopped / closed

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_surveys');
    }
};
