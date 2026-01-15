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
        // 1. event_speaker → event_speaking に統合
        DB::table('timeline_events')
            ->where('event_type', 'event_speaker')
            ->update([
                'event_type' => 'event_speaking',
                'source' => 'connpass',
            ]);
        
        // 2. event_organizer → event_hosting に統合
        DB::table('timeline_events')
            ->where('event_type', 'event_organizer')
            ->update([
                'event_type' => 'event_hosting',
                'source' => 'connpass',
            ]);
        
        // 3. Connpass由来のイベントからexternal_idを抽出
        $connpassEvents = DB::table('timeline_events')
            ->where('source', 'connpass')
            ->get();
        
        foreach ($connpassEvents as $event) {
            $payload = json_decode($event->payload, true);
            
            // payloadからevent_idを抽出
            if (isset($payload['event_id'])) {
                DB::table('timeline_events')
                    ->where('id', $event->id)
                    ->update(['external_id' => (string) $payload['event_id']]);
            }
        }
        
        // 4. Qiitaイベントにsourceを設定（既存データ用）
        DB::table('timeline_events')
            ->where('event_type', 'qiita')
            ->whereNull('source')
            ->update(['source' => 'qiita']);
        
        // 5. Qiitaイベントからexternal_idを抽出
        $qiitaEvents = DB::table('timeline_events')
            ->where('event_type', 'qiita')
            ->whereNull('external_id')
            ->get();
        
        foreach ($qiitaEvents as $event) {
            $payload = json_decode($event->payload, true);
            
            // payloadからitem_idを抽出
            if (isset($payload['item_id'])) {
                DB::table('timeline_events')
                    ->where('id', $event->id)
                    ->update(['external_id' => (string) $payload['item_id']]);
            }
        }
        
        // 6. フォーム由来のイベントにsourceを設定
        DB::table('timeline_events')
            ->whereIn('event_type', ['event_speaking', 'event_hosting', 'certification'])
            ->whereNull('source')
            ->update(['source' => 'form']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 元に戻す場合は、Connpass由来のものをevent_speaker/event_organizerに戻す
        DB::table('timeline_events')
            ->where('event_type', 'event_speaking')
            ->where('source', 'connpass')
            ->update(['event_type' => 'event_speaker']);
        
        DB::table('timeline_events')
            ->where('event_type', 'event_hosting')
            ->where('source', 'connpass')
            ->update(['event_type' => 'event_organizer']);
    }
};
