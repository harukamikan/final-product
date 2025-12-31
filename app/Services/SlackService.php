<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    protected $botToken;
    protected $defaultChannel;

    public function __construct()
    {
        $this->botToken = config('services.slack.notifications.bot_user_oauth_token');
        $this->defaultChannel = config('services.slack.notifications.channel');
    }

    /**
     * Slackチャンネルにメッセージを送信
     */
    public function sendMessage($message, $channel = null)
    {
        $channel = $channel ?? $this->defaultChannel;
        if (app()->environment('local') && !$this->botToken) {
                \Log::info('Slack message (local):', ['message' => $message, 'channel' => $channel]);
                return true; // 成功として扱う
            }
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->botToken,
                'Content-Type' => 'application/json',
            ])->post('https://slack.com/api/chat.postMessage', [
                'channel' => $channel,
                'text' => $message,
            ]);

            if ($response->successful() && $response->json('ok')) {
                Log::info('Slack notification sent successfully', ['channel' => $channel]);
                return true;
            }

            Log::error('Slack notification failed', [
                'response' => $response->json(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Slack notification error', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * 同姓同名エラーを管理者に通知
     */
    public function notifyDuplicateNameError($userName, $candidates = [])
    {
        $message = "⚠️ *同姓同名を検出しました*\n\n";
        $message .= "入力された名前: `{$userName}`\n";
        
        if (!empty($candidates)) {
            $message .= "該当候補:\n";
            foreach ($candidates as $candidate) {
                $message .= "• {$candidate}\n";
            }
        }
        
        $message .= "\n管理者による確認が必要です。";

        return $this->sendMessage($message);
    }

    /**
     * 個人にDMを送信
     */
    public function sendDM($slackUserId, $message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->botToken,
                'Content-Type' => 'application/json',
            ])->post('https://slack.com/api/chat.postMessage', [
                'channel' => $slackUserId,
                'text' => $message,
            ]);

            if ($response->successful() && $response->json('ok')) {
                Log::info('Slack DM sent successfully', ['user_id' => $slackUserId]);
                return true;
            }

            Log::error('Slack DM failed', [
                'user_id' => $slackUserId,
                'response' => $response->json(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Slack DM error', [
                'user_id' => $slackUserId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}