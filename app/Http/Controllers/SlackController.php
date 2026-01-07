<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mission;
use App\Models\UserMission;
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
        // Slack署名検証（必須）
        if (!$this->verifySlackSignature($request)) {
            Log::error('Slack signature verification failed');
            abort(401, 'Invalid Slack signature');
        }

        $slackUserId = (string) $request->input('user_id');
        
        // まずユーザー特定（Slackログイン済み前提）
        $user = User::where('slack_id', $slackUserId)->first();
        if (!$user) {
            return response()->json([
                "response_type" => "ephemeral",
                "text" => "ユーザー連携が見つかりませんでした。まずWebアプリでSlackログインしてから再度お試しください。\n\nログインURL: " . route('slack.login')
            ]);
        }

        // Check which missions are available (incomplete UserMissions)
        $missionKeys = [
            'qiita' => 'write_tech_blog',
            'event_plan' => 'event_organizer',
            'event_talk' => 'event_speaker',
            'cert' => 'acquire_certificate',
        ];

        $availableMissions = [];
        foreach ($missionKeys as $type => $key) {
            // Find mission template (shared OR personal for this user)
            $mission = Mission::withoutCompany()
                ->where('key', $key)
                ->where('company_id', $user->company_id)
                ->where(function ($q) use ($user) {
                    $q->whereNull('user_id')          // Shared missions (available to all)
                      ->orWhere('user_id', $user->id); // Personal missions for this user
                })
                ->first();

            Log::info("Checking mission availability", [
                'type' => $type,
                'key' => $key,
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'mission_found' => $mission ? 'yes' : 'no',
                'mission_id' => $mission ? $mission->id : null,
            ]);

            if (!$mission) {
                Log::warning("Mission not found", ['type' => $type, 'key' => $key, 'company_id' => $user->company_id]);
                continue;
            }

            // Check if user has incomplete UserMission for this mission
            // OR if UserMission doesn't exist yet (first time - should be allowed)
            $userMission = UserMission::withoutCompany()
                ->where('user_id', $user->id)
                ->where('mission_id', $mission->id)
                ->where('company_id', $user->company_id)
                ->first();

            Log::info("UserMission status", [
                'type' => $type,
                'user_mission_exists' => $userMission ? 'yes' : 'no',
                'user_mission_id' => $userMission ? $userMission->id : null,
                'completed_at' => $userMission ? $userMission->completed_at : null,
                'is_available' => (!$userMission || is_null($userMission->completed_at)) ? 'yes' : 'no',
            ]);

            // Include mission if:
            // 1) No UserMission exists yet (first time) OR
            // 2) UserMission exists and is incomplete (completed_at is null)
            if (!$userMission || is_null($userMission->completed_at)) {
                $availableMissions[$type] = [
                    'url' => $this->signedFormUrl($slackUserId, $type),
                    'mission' => $mission,
                ];
            }
        }

        Log::info("Available missions result", [
            'count' => count($availableMissions),
            'types' => array_keys($availableMissions),
        ]);

        // If no missions available
        if (empty($availableMissions)) {
            return response()->json([
                "response_type" => "ephemeral",
                "text" => "現在実行可能なミッションはありません。\n\nWebのミッション一覧を確認してください: " . route('missions.index')
            ]);
        }

        // Build button elements for available missions
        $buttonLabels = [
            'qiita' => '📝 技術ブログ(Qiita)',
            'event_talk' => '🎤 イベント登壇',
            'event_plan' => '🎪 イベント企画・開催',
            'cert' => '🎓 資格取得',
        ];

        $buttons = [];
        foreach ($availableMissions as $type => $data) {
            $label = $buttonLabels[$type] ?? $type;
            $button = [
                "type" => "button",
                "text" => ["type" => "plain_text", "text" => $label],
                "url" => $data['url']
            ];
            if ($type === 'qiita') {
                $button["style"] = "primary";
            }
            $buttons[] = $button;
        }

        // Split buttons into rows (2 per row)
        $actionBlocks = [];
        $buttonChunks = array_chunk($buttons, 2);
        foreach ($buttonChunks as $chunk) {
            $actionBlocks[] = [
                "type" => "actions",
                "elements" => $chunk
            ];
        }

        // Return Block Kit with available buttons
        return response()->json([
            "response_type" => "ephemeral",
            "blocks" => array_merge(
                [
                    [
                        "type" => "section",
                        "text" => ["type" => "mrkdwn", "text" => "*📋  ミッションメニュー*\n下のボタンから選んでWebフォームを開いてください。"]
                    ]
                ],
                $actionBlocks,
                [
                    [
                        "type" => "context",
                        "elements" => [[
                            "type" => "mrkdwn",
                            "text" => "💡 フォームURLは30分間有効です"
                        ]]
                    ]
                ]
            )
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

        // Qiita type: show dedicated URL form
        if ($type === 'qiita') {
            $mission = Mission::withoutCompany()
                ->where('key', 'write_tech_blog')
                ->where('company_id', $user->company_id)
                ->where(function ($q) use ($user) {
                    $q->whereNull('user_id')
                      ->orWhere('user_id', $user->id);
                })
                ->first();
                
            if (!$mission) {
                abort(404, 'Qiitaミッションが見つかりませんでした。');
            }

            // Check UserMission status
            // Allow if: 1) No UserMission (first time) OR 2) UserMission exists and incomplete
            $userMission = UserMission::withoutCompany()
                ->where('user_id', $user->id)
                ->where('mission_id', $mission->id)
                ->where('company_id', $user->company_id)
                ->first();

            // Block only if UserMission exists AND is completed
            if ($userMission && !is_null($userMission->completed_at)) {
                return view('missions.slack_mission_unavailable', [
                    'mission' => $mission,
                    'message' => 'このミッションはすでに完了しています。',
                    'detail' => 'Webのミッション一覧で他のミッションを確認してください。',
                ]);
            }

            return view('missions.slack_qiita_form', [
                'mission' => $mission,
                'token' => $token,
                'user' => $user,
            ]);
        }

        // Other types: show existing form
        $missionKey = match ($type) {
            'event_plan' => 'event_organizer',
            'event_talk' => 'event_speaker',
            'cert' => 'acquire_certificate',
            default => abort(404, '不正なミッションタイプです。'),
        };

        $mission = Mission::withoutCompany()
            ->where('key', $missionKey)
            ->where('company_id', $user->company_id)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            })
            ->first();
            
        if (!$mission) {
            abort(404, 'ミッションが見つかりませんでした。');
        }

        // Check UserMission status
        // Allow if: 1) No UserMission (first time) OR 2) UserMission exists and incomplete
        $userMission = UserMission::withoutCompany()
            ->where('user_id', $user->id)
            ->where('mission_id', $mission->id)
            ->where('company_id', $user->company_id)
            ->first();

        // Block only if UserMission exists AND is completed
        if ($userMission && !is_null($userMission->completed_at)) {
            return view('missions.slack_mission_unavailable', [
                'mission' => $mission,
                'message' => 'このミッションはすでに完了しています。',
                'detail' => 'Webのミッション一覧で他のミッションを確認してください。',
            ]);
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

        $mission = Mission::withoutCompany()
            ->where('key', $missionKey)
            ->where('company_id', $user->company_id)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            })
            ->first();
            
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
    $achievementData = $missionService->handleTrigger(
        $user,
        $mission->trigger_type,
        [
            'mission_key' => $mission->key,
            'url' => $request->evidence_url,
        ]
    );

    return view('missions.slack_mission_success', [
        'mission' => $mission,
        'earned' => $achievementData['earned_miles'],
    ]);
    }

    /**
     * Qiita URL submission from Slack
     */
    public function submitSlackQiitaForm(Request $request)
    {
        $token = $request->input('token');
        
        if (!$token) {
            return back()->withErrors(['token' => 'トークンが指定されていません。']);
        }

        $verified = $this->verifySignedToken($token, 'qiita');
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
            'url' => 'required|url',
        ]);

        $url = $request->input('url');
        
        // ミッション情報を取得
        $mission = Mission::withoutCompany()
            ->where('key', 'write_tech_blog')
            ->where('company_id', $user->company_id)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            })
            ->first();
            
        if (!$mission) {
            return back()->withErrors(['mission' => 'Qiitaミッションが見つかりませんでした。']);
        }

        // Check UserMission status
        // Allow if: 1) No UserMission (first time) OR 2) UserMission exists and incomplete
        $userMission = UserMission::withoutCompany()
            ->where('user_id', $user->id)
            ->where('mission_id', $mission->id)
            ->where('company_id', $user->company_id)
            ->first();

        // Block only if UserMission exists AND is completed
        if ($userMission && !is_null($userMission->completed_at)) {
            return back()->withErrors(['mission' => 'このミッションはすでに完了しています。']);
        }

        // Forward to existing MissionController logic (with all Qiita/Gemini processing)
        $missionController = app(MissionController::class);
        
        // Temporarily authenticate the user for this request
        Auth::login($user);
        
        // Delegate to existing submitBlogUrl method
        return $missionController->submitBlogUrl(
            $request,
            app(MissionService::class),
            app(\App\Services\QiitaService::class),
            app(\App\Services\GeminiService::class)
        );
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
