<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semester_settings', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });

        // 既存データに現在のユーザーの会社IDを設定
        $company = \App\Models\Company::first();
        if ($company) {
            \App\Models\SemesterSetting::whereNull('company_id')->update(['company_id' => $company->id]);
        }
    }

    public function down(): void
    {
        Schema::table('semester_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
