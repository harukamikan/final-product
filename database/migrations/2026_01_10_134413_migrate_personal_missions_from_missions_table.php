<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // missions テーブルから user_id がある（個人ミッション）をコピー
        $personalMissions = DB::table('missions')
            ->whereNotNull('user_id')
            ->get();

        foreach ($personalMissions as $mission) {
            DB::table('personal_missions')->insert([
                'user_id' => $mission->user_id,
                'company_id' => $mission->company_id ?? DB::table('users')->where('id', $mission->user_id)->value('company_id'),
                'key' => $mission->key,
                'title' => $mission->title,
                'description' => $mission->description,
                'trigger_type' => $mission->trigger_type,
                'required_count' => $mission->required_count,
                'reward_miles' => $mission->reward_miles,
                'repeatable' => $mission->repeatable,
                'created_at' => $mission->created_at,
                'updated_at' => $mission->updated_at,
            ]);
        }

        // missions テーブルから個人ミッションを削除
        DB::table('missions')->whereNotNull('user_id')->delete();
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ロールバック時は personal_missions を削除
        DB::table('personal_missions')->truncate();
    }
};