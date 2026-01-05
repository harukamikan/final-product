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
        Schema::create('onboarding_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Survey responses (Q1-Q4)
            $table->string('role'); // IC, TechLead, EM, PdM
            $table->string('preferred_output'); // blog, event, speaker, cert
            $table->string('current_situation'); // new, normal, busy
            $table->string('experience_level'); // junior, mid, senior
            
            // Calculated segment
            $table->string('segment'); // output_blog, community_event, speaker, skillup_cert
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['company_id', 'segment']);
            $table->unique('user_id'); // One survey per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_surveys');
    }
};
