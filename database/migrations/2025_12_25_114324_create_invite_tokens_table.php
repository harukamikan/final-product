<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invite_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('token', 80)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_uses')->default(0); // 0=無制限
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invite_tokens');
    }
};
