<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')
                        ->constrained()->nullOnDelete();
                $table->index('company_id');
            }
        });

        // qiita_articles
        Schema::table('qiita_articles', function (Blueprint $table) {
            if (!Schema::hasColumn('qiita_articles', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('user_id')
                        ->constrained()->nullOnDelete();
                $table->index(['company_id', 'created_at']);
            }
        });

        // missions
        Schema::table('missions', function (Blueprint $table) {
            if (!Schema::hasColumn('missions', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')
                        ->constrained()->nullOnDelete();
                $table->index('company_id');
            }
        });

        // user_missions
        Schema::table('user_missions', function (Blueprint $table) {
            if (!Schema::hasColumn('user_missions', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('user_id')
                        ->constrained()->nullOnDelete();
                $table->index(['company_id', 'user_id']);
            }
        });

        // mile_histories
        Schema::table('mile_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('mile_histories', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('user_id')
                        ->constrained()->nullOnDelete();
                $table->index(['company_id', 'user_id']);
            }
        });

        // goals
        Schema::table('goals', function (Blueprint $table) {
            if (!Schema::hasColumn('goals', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('user_id')
                        ->constrained()->nullOnDelete();
                $table->index(['company_id', 'user_id']);
            }
        });

        // activities
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('user_id')
                        ->constrained()->nullOnDelete();
                $table->index(['company_id', 'user_id']);
            }
        });
    }

    public function down(): void
    {
        // 外部キーとカラム削除は順序が大事（子→親の順）

        Schema::table('activities', function (Blueprint $table) {
            if (Schema::hasColumn('activities', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        Schema::table('goals', function (Blueprint $table) {
            if (Schema::hasColumn('goals', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        Schema::table('mile_histories', function (Blueprint $table) {
            if (Schema::hasColumn('mile_histories', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        Schema::table('user_missions', function (Blueprint $table) {
            if (Schema::hasColumn('user_missions', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        Schema::table('missions', function (Blueprint $table) {
            if (Schema::hasColumn('missions', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        Schema::table('qiita_articles', function (Blueprint $table) {
            if (Schema::hasColumn('qiita_articles', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });
    }
};
