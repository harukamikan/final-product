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
        Schema::table('semester_goals', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('user_id');
        });

        // 既存データに対して、ユーザーのcompany_idを設定
        DB::statement('
            UPDATE semester_goals sg
            INNER JOIN users u ON sg.user_id = u.id
            SET sg.company_id = u.company_id
            WHERE sg.company_id IS NULL
        ');

        // NOT NULL制約と外部キー制約を追加
        Schema::table('semester_goals', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semester_goals', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
