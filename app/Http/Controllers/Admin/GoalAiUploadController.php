<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClaudeService;
use App\Services\SlackService;
use App\Models\User;
use App\Models\SemesterGoal;

class GoalAiUploadController extends Controller
{
    protected $claudeService;
    protected $slackService; 

    public function __construct(ClaudeService $claudeService,SlackService $slackService)
    {
        $this->claudeService = $claudeService;
        $this->slackService = $slackService;
    }

    // アップロード画面を表示
    public function index()
    {
        return view('admin.goals.ai-upload');
    }

    // テキストファイルを処理
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:txt,xlsx,xls,docx'
        ]);

        // ファイルの内容を取得
        $file = $request->file('file');
        $content = $this->extractContent($file);

        // ユーザー一覧を取得
        $availableUsers = \App\Models\User::pluck('name')->toArray();
        \Log::info('=== Available Users ===', $availableUsers);

        try {
            // AI で目標を分類（新メソッド使用）
            $extractedData = $this->claudeService->classifyGoals($content, $availableUsers);

            // セッションに保存して確認画面へ
            session(['extracted_goals' => $extractedData]);

            return redirect()->route('admin.goals.ai.confirm');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'AI抽出に失敗しました: ' . $e->getMessage());
        }
    }

    // 確認画面を表示
    public function confirm()
    {
        $extractedData = session('extracted_goals');

        if (!$extractedData) {
            return redirect()->route('admin.goals.ai.index')->with('error', 'データがありません');
        }

        return view('admin.goals.ai-confirm', compact('extractedData'));
    }

    // 確認後、実際に登録
    public function store(Request $request)
    {
        $extractedData = session('extracted_goals');

        if (!$extractedData) {
            return redirect()->route('admin.goals.ai.index')->with('error', 'データがありません');
        }

        $successCount = 0;
        $errorUsers = [];
        $duplicateNameErrors = [];

        foreach ($extractedData as $userData) {
            $name = $userData['name'] ?? null;
            
            if (!$name) continue;

            // 同姓同名エラーをチェック
            if ($name === 'error_duplicate_name') {
                $originalInput = $userData['original_input'] ?? '不明';
                $candidates = $userData['candidates'] ?? [];
                 if (empty($candidates)) {
                    // 未登録ユーザー
                    $errorMessage = '登録されていないユーザー名です: ' . $originalInput;
                    $duplicateNameErrors[] = $errorMessage;
                    
                    // 管理者にSlack通知
                    $this->slackService->notifyUnregisteredUser($originalInput);
                } else {
                    // 同姓同名
                    $errorMessage = '同姓同名のユーザーが存在します: ' . $originalInput;
                    $duplicateNameErrors[] = $errorMessage;
                           
                // 管理者にSlack通知を送信
                $this->slackService->notifyDuplicateNameError($originalInput, $candidates);
                
                // 該当ユーザー全員にDMを送信
                foreach ($candidates as $candidateName) {
                    $user = User::where('name', $candidateName)->first();
                    if ($user && $user->slack_id) {
                        $this->slackService->sendDM(
                            $user->slack_id,
                            "⚠️ 半期目標の登録に失敗しました。\n\n同姓同名のため、次回から社員番号やメールアドレスも記入してください。\n\n目標登録：" . config('app.url') . "/semester-goals/create"
                        );
                    }
                }

            }
                
                \Log::error('同姓同名エラー検出', [
                    'original_input' => $originalInput,
                    'candidates' => $candidates
                ]);
                
                continue;
            }

            // ユーザーを名前で検索（3段階）
            $user = User::where('name', $name)->first();

            if (!$user) {
                $normalized = mb_convert_kana($name, 'as', 'UTF-8');
                $user = User::where('name', $normalized)->first();
            }

            if (!$user) {
                $user = User::where('name', 'like', "%{$name}%")->first();
            }

            if (!$user) {
                $errorUsers[] = $name;
                continue;
            }

            // 1. semester_goals 登録（定性的な目標）
            if (isset($userData['semester_goal']) && $userData['semester_goal']) {
                SemesterGoal::create([
                    'user_id' => $user->id,
                    'category' => '半期目標',
                    'title' => $userData['semester_goal'],
                    'deadline' => $userData['deadline'] ?? null,
                    'is_current' => true,
                ]);
                $successCount++;
            }

            // 2. missions 登録（定量的な目標）
            if (isset($userData['missions']) && is_array($userData['missions'])) {
                foreach ($userData['missions'] as $mission) {
                    \App\Models\Mission::create([
                        'user_id' => $user->id,
                        'key' => \Illuminate\Support\Str::slug($mission['title'] ?? ''),
                        'title' => $mission['title'] ?? '',
                        'description' => "個人目標: " . ($mission['category'] ?? ''),
                        'trigger_type' => 'manual',
                        'required_count' => $mission['count'] ?? 1,
                        'reward_miles' => 0,
                        'repeatable' => false,
                    ]);
                    $successCount++;
                }
            }
        }

        // セッションをクリア
        session()->forget('extracted_goals');

        $errorUsers = array_unique($errorUsers);
        $messages = [
            'success' => "{$successCount}件の目標を登録しました"
        ];

        if (!empty($duplicateNameErrors)) {
            $messages['duplicate_errors'] = $duplicateNameErrors;
        }

        if (!empty($errorUsers)) {
            $messages['errors'] = $errorUsers;
        }

        return redirect()->route('admin.goals.ai.index')->with($messages);
    }

    // ファイルから内容を抽出
    protected function extractContent($file)
    {
        $extension = $file->getClientOriginalExtension();

        if ($extension === 'txt') {
            return file_get_contents($file->getRealPath());
        }

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Excel の場合は全セルを結合
            $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file)[0];
            $content = '';
            foreach ($data as $row) {
                $content .= implode(' ', $row) . "\n";
            }
            return $content;
        }

        throw new \Exception('Unsupported file type');
    }
}