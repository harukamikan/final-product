<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClaudeService;
use App\Models\User;
use App\Models\SemesterGoal;

class GoalAiUploadController extends Controller
{
    protected $claudeService;

    public function __construct(ClaudeService $claudeService)
    {
        $this->claudeService = $claudeService;
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

    // 🆕 ユーザー一覧を取得
    $availableUsers = \App\Models\User::pluck('name')->toArray();

    try {
        // AI で目標を抽出（ユーザー一覧を渡す）
        $extractedData = $this->claudeService->extractGoals($content, $availableUsers);

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

    foreach ($extractedData as $userData) {
        $name = $userData['name'] ?? null;
        $goals = $userData['goals'] ?? [];

        if (!$name) continue;

        // 🆕 ユーザーを名前で検索（完全一致）
        $user = User::where('name', $name)->first();

        // 🆕 見つからなければ部分一致で探す
        if (!$user) {
            $user = User::where('name', 'like', "%{$name}%")->first();
        }

        if ($user) {
            foreach ($goals as $goal) {
                SemesterGoal::create([
                    'user_id' => $user->id,
                    'category' => $goal['category'] ?? '',
                    'title' => $goal['title'] ?? '',
                    'deadline' => $goal['deadline'] ?? null,
                ]);
                $successCount++;
            }
        } else {
            $errorUsers[] = $name;
        }
    }

    // セッションをクリア
    session()->forget('extracted_goals');

    $errorUsers = array_unique($errorUsers);

    return redirect()->route('admin.goals.ai.index')->with([
        'success' => "{$successCount}件の目標を登録しました",
        'errors' => $errorUsers
    ]);
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