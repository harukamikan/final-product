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
    public function extractGoals($text, $availableUsers = [])
    {
        $prompt = $this->buildPrompt($text, $availableUsers);

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
            
            \Log::info('=== AI Response ===', ['content' => $content]);
            
            return $this->parseResponse($content);
        }

        throw new \Exception('Claude API request failed: ' . $response->body());
    }

    /**
     * 半期目標を定性的/定量的に分類
     */
    public function classifyGoals($text, $availableUsers = [])
    {
        $prompt = $this->buildClassifyPrompt($text, $availableUsers);

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
            
            \Log::info('=== Claude Classify Response ===', ['content' => $content]);
            
            return $this->parseResponse($content);
        }

        throw new \Exception('Claude API request failed: ' . $response->body());
    }

    /**
     * プロンプトを構築
     */
    protected function buildPrompt($text, $availableUsers = [])
    {
        \Log::info('=== AI Input ===');
        \Log::info('Text:', ['text' => $text]);
        \Log::info('Available Users:', $availableUsers);
        
        $userList = '';
        if (!empty($availableUsers)) {
            $userList = "\n# 【重要】利用可能なユーザー名リスト:\n" . implode("\n", $availableUsers);
        }
        
        return <<<PROMPT
あなたはエンジニアの半期目標を抽出するアシスタントです。

以下のテキストから、各メンバーの目標情報を抽出して、JSON形式で返してください。
{$userList}

# 【重要】名前の抽出ルール
1. 入力テキストに記載されている名前を確認する
2. 上記の「利用可能なユーザー名リスト」から、**完全一致**で該当するユーザーを探す
3. 完全一致がない場合は、入力テキストの名前をそのまま使用する

# 抽出ルール
- 名前（name）: 上記のルールに従って正確に抽出
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
    }

    /**
     * 分類用プロンプトを構築
     */
    protected function buildClassifyPrompt($text, $availableUsers = [])
    {
        $userList = '';
        if (!empty($availableUsers)) {
            $userList = "\n# 【重要】利用可能なユーザー名リスト:\n" . implode("\n", $availableUsers);
        }
        
        return <<<PROMPT
あなたは半期目標を分析するアシスタントです。
{$userList}

【タスク】
以下のテキストから、各メンバーの目標を抽出して分類してください。

# 名前の抽出ルール
- 入力テキストの最初の行が名前です
- 上記の「利用可能なユーザー名リスト」から、最も近いものを選択してください

# 分類ルール

**定量的な目標（missions）:**
- 数値がある（○本、○回、○個など）
- 例: ブログ5本、資格2個取得、登壇3回

**定性的な目標（semester_goal）:**
- 数値なし
- 例: 商談を成功させる、スキルアップする

# 入力テキスト
{$text}

# 出力例
```json
[
  {
    "name": "haruka",
    "semester_goal": "商談を成功させる",
    "missions": [
      {"category": "ブログ", "title": "ブログ投稿", "count": 5},
      {"category": "資格", "title": "一陸技取得", "count": 1}
    ]
  }
]
```

必ずこの形式のJSONのみを返してください。説明文は不要です。
PROMPT;
    }

    /**
     * レスポンスをパース
     */
    protected function parseResponse($content)
    {
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        $content = trim($content);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse JSON response');
        }

        return $data;
    }
}