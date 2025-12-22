<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClaudeService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * 自由形式のテキストから目標を抽出
     */
    public function extractGoals($text,$availableUsers = [])
    {
<<<<<<< HEAD
        $prompt = $this->buildPrompt($text, $availableUsers);
=======
        $prompt = $this->buildPrompt($text,$availableUsers);
>>>>>>> develop

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post($this->apiUrl, [
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $content = $result['content'][0]['text'] ?? '';
            
            // JSON部分を抽出
            return $this->parseResponse($content);
        }

        throw new \Exception('Claude API request failed: ' . $response->body());
    }

/**
 * プロンプトを構築
 */
protected function buildPrompt($text, $availableUsers = [])
{
<<<<<<< HEAD
    // デバッグ用
    \Log::info('Available Users:', $availableUsers);
    $userList = '';
    if (!empty($availableUsers)) {
        $userList = "\n# 【重要】利用可能なユーザー名リスト:\n" . implode("\n", $availableUsers);
    }
     // デバッグ用
    \Log::info('User List:', ['userList' => $userList]);
=======
    $userList = '';
    if (!empty($availableUsers)) {
        $userList = "\n# 利用可能なユーザー名:\n" . implode("\n", $availableUsers);
    }
>>>>>>> develop
    
    return <<<PROMPT
あなたはエンジニアの半期目標を抽出するアシスタントです。

以下のテキストから、各メンバーの目標情報を抽出して、JSON形式で返してください。
{$userList}

# 【重要】名前の抽出ルール
1. 入力テキストに記載されている名前を確認する
2. 上記の「利用可能なユーザー名リスト」から、**完全一致**で該当するユーザーを探す
3. 完全一致がない場合は、入力テキストの名前をそのまま使用する

例:
- 入力テキスト: "haruka:" 
- 利用可能なユーザー名: ["test", "haruka", "haruka", "haruka"]
- 出力: "haruka" ✅（リストから完全一致を選択）
# 抽出ルール
<<<<<<< HEAD
- 名前（name）: 上記のルールに従って正確に抽出
=======
- 名前（name）: メンバーの名前（必ず上記の利用可能なユーザー名から選択してください）
>>>>>>> develop
- 目標（goals）: 配列形式で複数可
  - category: ブログ、資格、登壇、開発、学習 など
  - title: 目標の内容（具体的に）
  - deadline: 期限（YYYY-MM-DD形式、不明な場合はnull）

# 入力テキスト
{$text}

# 出力形式（必ずこの形式で返してください）
```json
[
  {
    "name": "完全なユーザー名",
    "goals": [
      {
        "category": "カテゴリ",
        "title": "目標内容",
        "deadline": "2025-06-30"
      }
    ]
  }
]
```

JSONのみを返してください。説明文は不要です。
PROMPT;
}    /**
     * レスポンスをパース
     */
    protected function parseResponse($content)
    {
        // ```json ... ``` を削除
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        $content = trim($content);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse JSON response');
        }

        return $data;
    }

    /**
     * 目標を定量的・定性的に分類
     */
    public function classifyGoals($goals)
    {
        $prompt = $this->buildClassificationPrompt($goals);

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post($this->apiUrl, [
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $content = $result['content'][0]['text'] ?? '';
            return $this->parseResponse($content);
        }

        throw new \Exception('Claude API request failed: ' . $response->body());
    }

    /**
     * 分類用プロンプトを構築
     */
    protected function buildClassificationPrompt($goals)
    {
        $goalsJson = json_encode($goals, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return <<<PROMPT
あなたは目標を分類するアシスタントです。

以下の目標リストを、**定量的な目標**と**定性的な目標**に分類してください。

# 分類基準

## 定量的な目標（missions）
- 数値で測定可能
- 繰り返し可能
- 具体的なアクション

**例:**
- ブログを5本書く → missions（trigger_type: "blog", required_count: 5）
- 資格を2個取得 → missions（trigger_type: "certification", required_count: 2）
- 登壇を3回行う → missions（trigger_type: "presentation", required_count: 3）

## 定性的な目標（semester_goals）
- 数値化困難
- 一度きりの達成
- 抽象的・概念的

**例:**
- チームリーダーとしてスキルアップ
- 商談を成功させる
- 新技術を習得する

# 入力目標
{$goalsJson}

# 出力形式
```json
{
  "missions": [
    {
      "title": "ブログを5本書く",
      "trigger_type": "blog",
      "required_count": 5,
      "key": "blog_5"
    }
  ],
  "semester_goals": [
    {
      "goal": "チームリーダーとしてスキルアップ"
    }
  ]
}
```

**trigger_typeの種類:**
- blog（ブログ執筆）
- certification（資格取得）
- presentation（登壇・発表）
- development（開発プロジェクト）
- study（学習・研修）

JSONのみを返してください。説明文は不要です。
PROMPT;
    }
}