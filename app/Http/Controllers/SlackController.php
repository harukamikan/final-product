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


        // Get all missions available to this user (same logic as Web)
        $allMissions = Mission::withoutCompany()
            ->where(function ($q) use ($user) {
                // Include:
                // 1) Global missions (company_id is null - created by Seeder)
                // 2) Company-specific missions (company_id matches user's company)
                $q->whereNull('company_id')
                  ->orWhere('company_id', $user->company_id);
            })
            ->where(function ($q) use ($user) {
                // Include:
                // 1) Shared missions (user_id is null)
                // 2) Personal missions (user_id matches current user)
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            })
            ->with(['userMissions' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->orderBy('id')
            ->get();

        Log::info("Fetched missions for Slack", [
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'total_missions' => $allMissions->count(),
        ]);

        // Filter to incomplete missions only
        $incompleteMissions = $allMissions->filter(function ($mission) {
            $userMission = $mission->userMissions->first();
            
            // Include if:
            // 1) No UserMission exists yet (first time) OR
            // 2) UserMission exists and is incomplete (completed_at is null)
            return !$userMission || is_null($userMission->completed_at);
        });

        Log::info("Incomplete missions for Slack", [
            'incomplete_count' => $incompleteMissions->count(),
            'mission_ids' => $incompleteMissions->pluck('id')->toArray(),
        ]);


        // If no missions available
        if ($incompleteMissions->isEmpty()) {
            return response()->json([
                "response_type" => "ephemeral",
                "text" => "現在実行可能なミッションはありません。\n\nWebのミッション一覧を確認してください: " . route('missions.index')
            ]);
        }

        // Build rich section blocks for each mission with details
        $missionBlocks = [];
        foreach ($incompleteMissions as $mission) {
            // Determine mission type and form URL based on trigger_type and key
            $type = $this->getMissionType($mission);
            $buttonLabel = $this->getButtonLabel($mission);
            $emoji = $this->getMissionEmoji($mission);
            
            // Build mission detail text
            $missionText = "*{$emoji} {$mission->title}*\n";
            $missionText .= "{$mission->description}\n";
            $missionText .= "💰 *報酬:* {$mission->reward_miles} mile";
            
            // Create section with button as accessory
            $button = [
                "type" => "button",
                "text" => ["type" => "plain_text", "text" => $buttonLabel],
                "url" => $this->signedFormUrl($slackUserId, $type, $mission->id)
            ];
            
            // Highlight tech blog missions
            if ($type === 'qiita' || $mission->trigger_type === 'tech_blog_posted') {
                $button["style"] = "primary";
            }
            
            $missionBlocks[] = [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => $missionText
                ],
                "accessory" => $button
            ];
            
            // Add divider between missions (except after last one)
            if ($mission !== $incompleteMissions->last()) {
                $missionBlocks[] = ["type" => "divider"];
            }
        }

        // Return Block Kit with rich mission details
        return response()->json([
            "response_type" => "ephemeral",
            "blocks" => array_merge(
                [
                    [
                        "type" => "section",
                        "text" => ["type" => "mrkdwn", "text" => "*📋 進行中のミッション一覧*\n各ミッションの詳細を確認して、ボタンからWebフォームを開いてください。"]
                    ],
                    ["type" => "divider"]
                ],
                $missionBlocks,
                [
                    ["type" => "divider"],
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

    private function signedFormUrl(string $slackUserId, string $type, int $missionId): string
    {
        $token = $this->makeSignedToken($slackUserId, $type, $missionId);
        return rtrim(config('app.url'), '/') . "/slack/missions/{$type}?token={$token}";
    }

    private function makeSignedToken(string $slackUserId, string $type, int $missionId): string
    {
        $expires = now()->addMinutes(30)->timestamp;
        $payload = "{$slackUserId}|{$type}|{$missionId}|{$expires}";
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

        [$slackUserId, $type, $missionId, $expires] = $verified;

        // ユーザー特定
        $user = User::where('slack_id', $slackUserId)->first();
        if (!$user) {
            abort(404, 'ユーザー連携が見つかりませんでした。まずWebアプリでSlackログインしてください。');
        }

        // Get mission by ID (no need to search by key anymore)
        $mission = Mission::withoutCompany()
            ->where('id', $missionId)
            ->where(function ($q) use ($user) {
                // Verify mission belongs to this company or is global
                $q->whereNull('company_id')
                  ->orWhere('company_id', $user->company_id);
            })
            ->where(function ($q) use ($user) {
                // Verify mission is accessible to this user
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

        // Determine which form to show based on trigger_type
        if ($mission->trigger_type === 'tech_blog_posted') {
            return view('missions.slack_qiita_form', [
                'mission' => $mission,
                'token' => $token,
                'user' => $user,
            ]);
        }

        // Default: show generic mission form
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

        [$slackUserId, $type, $missionId, $expires] = $verified;

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

        // Get mission by ID
        $mission = Mission::withoutCompany()
            ->where('id', $missionId)
            ->where(function ($q) use ($user) {
                $q->whereNull('company_id')
                  ->orWhere('company_id', $user->company_id);
            })
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

        [$slackUserId, $type, $missionId, $expires] = $verified;

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
        
        // Get mission by ID
        $mission = Mission::withoutCompany()
            ->where('id', $missionId)
            ->where(function ($q) use ($user) {
                $q->whereNull('company_id')
                  ->orWhere('company_id', $user->company_id);
            })
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
     * @return array|false [slackUserId, type, missionId, expires] or false
     */
    private function verifySignedToken(string $token, string $expectedType)
    {
        try {
            $decoded = base64_decode($token, true);
            if (!$decoded) {
                return false;
            }

            $parts = explode('|', $decoded);
            if (count($parts) !== 5) {
                return false;
            }

            [$slackUserId, $type, $missionId, $expires, $sig] = $parts;

            // タイプチェック
            if ($type !== $expectedType) {
                return false;
            }

            // 期限チェック
            if (time() > (int)$expires) {
                return false;
            }

            // 署名チェック
            $payload = "{$slackUserId}|{$type}|{$missionId}|{$expires}";
            $expectedSig = hash_hmac('sha256', $payload, (string) config('app.key'));
            
            if (!hash_equals($expectedSig, $sig)) {
                return false;
            }

            return [$slackUserId, $type, $missionId, $expires];
        } catch (\Exception $e) {
            Log::warning('Token verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Determine mission type for URL generation based on mission properties
     */
    private function getMissionType(Mission $mission): string
    {
        // Match by key first (for known mission types)
        return match ($mission->key) {
            'write_tech_blog' => 'qiita',
            'event_organizer' => 'event_plan',
            'event_speaker' => 'event_talk',
            'acquire_certificate' => 'cert',
            // Default: use trigger_type to determine form type
            default => match ($mission->trigger_type) {
                'tech_blog_posted' => 'qiita',
                'google_form_submitted' => 'event_plan', // generic form
                default => 'event_plan', // fallback to generic form
            },
        };
    }

    /**
     * Get button label based on mission type
     */
    private function getButtonLabel(Mission $mission): string
    {
        return match ($mission->trigger_type) {
            'tech_blog_posted' => '📝 URLを送信',
            'google_form_submitted' => '📝 入力する',
            default => '✅ 詳細を入力',
        };
    }

    /**
     * Get emoji based on mission key or type
     */
    private function getMissionEmoji(Mission $mission): string
    {
        // Match by key first
        $emojiByKey = match ($mission->key) {
            'write_tech_blog' => '📝',
            'event_organizer' => '🎪',
            'event_speaker' => '🎤',
            'acquire_certificate' => '🎓',
            default => null,
        };

        if ($emojiByKey) {
            return $emojiByKey;
        }

        // Fallback to trigger_type
        return match ($mission->trigger_type) {
            'tech_blog_posted' => '📝',
            'google_form_submitted' => '📋',
            default => '✨',
        };
    }

}
