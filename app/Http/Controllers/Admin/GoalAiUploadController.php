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

        try {
            // AI で目標を抽出
            $extractedData = $this->claudeService->extractGoals($content);

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

        // 🆕 ユーザーを名前で検索（3段階）
        
        // ①元の名前で完全一致
        $user = User::where('name', $name)->first();

        // ②全角→半角変換して完全一致
        if (!$user) {
            $normalized = mb_convert_kana($name, 'as', 'UTF-8');
            $user = User::where('name', $normalized)->first();
        }

        // ③部分一致（セーフティネット）
        if (!$user) {
            $user = User::where('name', 'like', "%{$name}%")->first();
        }

        $successCount = 0;
        $errorUsers = [];

        foreach ($extractedData as $userData) {
            $name = $userData['name'] ?? null;
            $goals = $userData['goals'] ?? [];

            if (!$name) continue;

            // ユーザーを名前で検索
            $user = User::where('name', $name)->first();

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