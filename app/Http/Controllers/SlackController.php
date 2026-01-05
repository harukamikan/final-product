<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mission;
use App\Models\MissionForm;
use App\Services\MissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SlackController extends Controller
{
    
    public function redirect()
    {
        return Socialite::driver('slack')->redirect();
    }

    
    public function callback()
    {
        try {
            $slackUser = Socialite::driver('slack')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors([
                'slack' => 'Slackログインに失敗しました。',
            ]);
        }

        $email    = $slackUser->getEmail();
        $name     = $slackUser->getName() ?? 'Slack User';
        $slackId  = $slackUser->getId();
        $avatar   = $slackUser->getAvatar();

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'slack_id' => $slackId,
                'password' => bcrypt(Str::random(32)), 
            ]);
        } else {
            if (!$user->slack_id) {
                $user->update(['slack_id' => $slackId]);
            }
        }

        Auth::login($user);

        return redirect('/dashboard');
    }

    public function commands(Request $request)
    {
        // デバッグログ追加
        Log::info('Slack command received', [
            'all_data' => $request->all(),
            'headers' => [
                'timestamp' => $request->header('X-Slack-Request-Timestamp'),
                'signature' => $request->header('X-Slack-Signature'),
            ]
        ]);

        // Slack署名検証（必須）
        if (!$this->verifySlackSignature($request)) {
            Log::error('Slack signature verification failed');
            abort(401, 'Invalid Slack signature');
        }

        Log::info('Slack signature verified successfully');

        $text = trim((string) $request->input('text', ''));
        $slackUserId = (string) $request->input('user_id'); // "UXXXX..."
        $appUrl = rtrim(config('app.url'), '/');

        // まずユーザー特定（Slackログイン済み前提）
        $user = User::where('slack_id', $slackUserId)->first();
        if (!$user) {
            return response()->json([
                "response_type" => "ephemeral",
                "text" => "ユーザー連携が見つかりませんでした。まずWebアプリでSlackログインしてから再度お試しください。"
            ]);
        }

        // /mission qiita <URL>
        if (Str::startsWith($text, 'qiita')) {
            $url = trim(Str::after($text, 'qiita'));

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return response()->json([
                    "response_type" => "ephemeral",
                    "text" => "URLが正しくないかもです。例：`/mission qiita https://qiita.com/...`"
                ]);
            }

            // MissionServiceを使ってミッション進捗を処理
            $mission = Mission::where('key', 'write_tech_blog')->first();
            if (!$mission) {
                return response()->json([
                    "response_type" => "ephemeral",
                    "text" => "❌ Qiitaミッションが見つかりませんでした。"
                ]);
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

            return response()->json([
                "response_type" => "ephemeral",
                "text" => $message
            ]);
        }

        // /mission （引数なし）：フォームへ飛ぶボタンを返す
        $links = [
            'event_plan' => $this->signedFormUrl($slackUserId, 'event_plan'),
            'event_talk' => $this->signedFormUrl($slackUserId, 'event_talk'),
            'cert'       => $this->signedFormUrl($slackUserId, 'cert'),
        ];

        return response()->json([
            "response_type" => "ephemeral",
            "blocks" => [
                [
                    "type" => "section",
                    "text" => ["type" => "mrkdwn", "text" => "*ミッションメニュー*\n下のボタンからフォームを開いて送信するとミッション完了になります。"]
                ],
                [
                    "type" => "actions",
                    "elements" => [
                        ["type" => "button", "text" => ["type" => "plain_text", "text" => "イベント企画・開催"], "url" => $links['event_plan']],
                        ["type" => "button", "text" => ["type" => "plain_text", "text" => "イベント登壇"],     "url" => $links['event_talk']],
                        ["type" => "button", "text" => ["type" => "plain_text", "text" => "資格取得"],         "url" => $links['cert']],
                    ]
                ],
                [
                    "type" => "context",
                    "elements" => [[
                        "type" => "mrkdwn",
                        "text" => "Qiitaは `/mission qiita <URL>` で送ると完了します。"
                    ]]
                ]
            ]
        ]);
    }

    private function verifySlackSignature(Request $request): bool
    {
        $timestamp = $request->header('X-Slack-Request-Timestamp');
        $signature = $request->header('X-Slack-Signature');

        if (!$timestamp || !$signature) return false;

        // リプレイ攻撃対策（5分以上ズレてたら拒否）
        if (abs(time() - (int)$timestamp) > 60 * 5) return false;

        $baseString = 'v0:' . $timestamp . ':' . $request->getContent();
        $mySignature = 'v0=' . hash_hmac('sha256', $baseString, (string) config('services.slack.signing_secret'));

        return hash_equals($mySignature, $signature);
    }

    private function signedFormUrl(string $slackUserId, string $type): string
    {
        $token = $this->makeSignedToken($slackUserId, $type);
        return rtrim(config('app.url'), '/') . "/slack/missions/{$type}?token={$token}";
    }

    private function makeSignedToken(string $slackUserId, string $type): string
    {
        $expires = now()->addMinutes(30)->timestamp;
        $payload = "{$slackUserId}|{$type}|{$expires}";
        $sig = hash_hmac('sha256', $payload, (string) config('app.key'));
        return base64_encode($payload . '|' . $sig);
    }

    /**
     * Slackから署名付きURLでアクセスするフォーム表示
     */
    public function showSlackForm(Request $request, string $type)
    {
        $token = $request->query('token');
        
        if (!$token) {
            abort(403, 'トークンが指定されていません。');
        }

        $verified = $this->verifySignedToken($token, $type);
        if (!$verified) {
            abort(403, 'トークンが無効または期限切れです。Slackから再度アクセスしてください。');
        }

        [$slackUserId, $type, $expires] = $verified;

        // ユーザー特定
        $user = User::where('slack_id', $slackUserId)->first();
        if (!$user) {
            abort(404, 'ユーザー連携が見つかりませんでした。まずWebアプリでSlackログインしてください。');
        }

        // ミッション情報を取得
        $missionKey = match ($type) {
            'event_plan' => 'event_organizer',
            'event_talk' => 'event_speaker',
            'cert' => 'acquire_certificate',
            default => abort(404, '不正なミッションタイプです。'),
        };

        $mission = Mission::where('key', $missionKey)->first();
        if (!$mission) {
            abort(404, 'ミッションが見つかりませんでした。');
        }

        return view('missions.slack_mission_form', [
            'mission' => $mission,
            'type' => $type,
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Slackから送信されたフォームを処理
     */
    public function submitSlackForm(Request $request, string $type)
    {
        $token = $request->input('token');
        
        if (!$token) {
            return back()->withErrors(['token' => 'トークンが指定されていません。']);
        }

        $verified = $this->verifySignedToken($token, $type);
        if (!$verified) {
            return back()->withErrors(['token' => 'トークンが無効または期限切れです。Slackから再度アクセスしてください。']);
        }

        [$slackUserId, $type, $expires] = $verified;

        // ユーザー特定
        $user = User::where('slack_id', $slackUserId)->first();
        if (!$user) {
            return back()->withErrors(['user' => 'ユーザー連携が見つかりませんでした。']);
        }

        // バリデーション
        $request->validate([
            'title' => 'required|string|max:255',
            'occurred_on' => 'required|date',
            'details' => 'nullable|string',
            'evidence_url' => 'nullable|url',
        ]);

        // ミッション情報を取得
        $missionKey = match ($type) {
            'event_plan' => 'event_organizer',
            'event_talk' => 'event_speaker',
            'cert' => 'acquire_certificate',
            default => abort(404, '不正なミッションタイプです。'),
        };

        $mission = Mission::where('key', $missionKey)->first();
        if (!$mission) {
            return back()->withErrors(['mission' => 'ミッションが見つかりませんでした。']);
        }

        // 1) フォーム提出を保存
        MissionForm::create([
            'user_id' => $user->id,
            'mission_id' => $mission->id,
            'category' => $mission->key,
            'title' => $request->title,
            'occurred_on' => $request->occurred_on,
            'details' => $request->details,
            'evidence_url' => $request->evidence_url,
        ]);

        // 2) ミッション達成処理
        $missionService = app(MissionService::class);
        $earned = $missionService->handleTrigger(
            $user,
            $mission->trigger_type,
            [
                'mission_key' => $mission->key,
                'url' => $request->evidence_url,
            ]
        );

        return view('missions.slack_mission_success', [
            'mission' => $mission,
            'earned' => $earned,
        ]);
    }

    /**
     * 署名付きトークンを検証
     * @return array|false [slackUserId, type, expires] or false
     */
    private function verifySignedToken(string $token, string $expectedType)
    {
        try {
            $decoded = base64_decode($token, true);
            if (!$decoded) {
                return false;
            }

            $parts = explode('|', $decoded);
            if (count($parts) !== 4) {
                return false;
            }

            [$slackUserId, $type, $expires, $sig] = $parts;

            // タイプチェック
            if ($type !== $expectedType) {
                return false;
            }

            // 期限チェック
            if (time() > (int)$expires) {
                return false;
            }

            // 署名チェック
            $payload = "{$slackUserId}|{$type}|{$expires}";
            $expectedSig = hash_hmac('sha256', $payload, (string) config('app.key'));
            
            if (!hash_equals($expectedSig, $sig)) {
                return false;
            }

            return [$slackUserId, $type, $expires];
        } catch (\Exception $e) {
            Log::warning('Token verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

}
