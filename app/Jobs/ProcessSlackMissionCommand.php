<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Mission;
use App\Services\MissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessSlackMissionCommand implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $slackUserId,
        private string $text,
        private string $responseUrl
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing Slack mission command', [
            'slack_user_id' => $this->slackUserId,
            'text' => $this->text,
        ]);

        try {
            // ユーザー特定
            $user = User::where('slack_id', $this->slackUserId)->first();
            
            if (!$user) {
                $this->sendSlackResponse([
                    'response_type' => 'ephemeral',
                    'text' => 'ユーザー連携が見つかりませんでした。まずWebアプリでSlackログインしてから再度お試しください。'
                ]);
                return;
            }

            // /mission qiita <URL>
            if (Str::startsWith($this->text, 'qiita')) {
                $this->handleQiitaMission($user);
                return;
            }

            // その他のコマンドは既に処理済み(即時応答)
            Log::warning('Unexpected command in job', ['text' => $this->text]);

        } catch (\Exception $e) {
            Log::error('Error processing Slack mission command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->sendSlackResponse([
                'response_type' => 'ephemeral',
                'text' => '❌ 処理中にエラーが発生しました。しばらくしてから再度お試しください。'
            ]);
        }
    }

    /**
     * Handle Qiita mission submission
     */
    private function handleQiitaMission(User $user): void
    {
        $url = trim(Str::after($this->text, 'qiita'));

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->sendSlackResponse([
                'response_type' => 'ephemeral',
                'text' => 'URLが正しくないかもです。例：`/mission qiita https://qiita.com/...`'
            ]);
            return;
        }

        // MissionServiceを使ってミッション進捗を処理
        $mission = Mission::where('key', 'write_tech_blog')->first();
        
        if (!$mission) {
            $this->sendSlackResponse([
                'response_type' => 'ephemeral',
                'text' => '❌ Qiitaミッションが見つかりませんでした。'
            ]);
            return;
        }

        $missionService = app(MissionService::class);
        $earned = $missionService->handleTrigger(
            $user,
            'tech_blog_posted',
            [
                'mission_key' => $mission->key,
                'url' => $url,
            ]
        );

        $message = "✅ Qiitaミッションを完了しました！（URL受領）";
        if ($earned > 0) {
            $message .= "\n🎉 {$earned}マイルを獲得しました！";
        }

        $this->sendSlackResponse([
            'response_type' => 'ephemeral',
            'text' => $message
        ]);
    }

    /**
     * Send response back to Slack via response_url
     */
    private function sendSlackResponse(array $payload): void
    {
        try {
            $response = Http::post($this->responseUrl, $payload);
            
            if (!$response->successful()) {
                Log::error('Failed to send Slack response', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception sending Slack response', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
