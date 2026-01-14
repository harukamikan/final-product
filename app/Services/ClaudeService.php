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
     * 半期目標を定性的/定量的に分類（同姓同名対策あり）
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
     * 分類用プロンプトを構築（同姓同名対策あり）
     */
    protected function buildClassifyPrompt($text, $availableUsers = [])
    {
        $userList = '';
        if (!empty($availableUsers)) {
            $userList = "\n【重要】利用可能なユーザー名リスト:\n" . implode("\n", $availableUsers);
        }

        return <<<PROMPT
あなたは半期目標を分析するアシスタントです。

【最優先】名前の判定ルール（3段階検索）

入力テキストの最初の行から名前を抽出して、以下の順序で照合してください：

**ステップ1: 完全一致を探す**
- 入力テキストの名前が、利用可能なユーザー名リストに完全一致するか確認
- 完全一致した → その名前を使用（終了）
- 完全一致しない → ステップ2へ

**ステップ2: 部分一致で1人だけ該当するか確認**
- 入力テキストの名前が、利用可能なユーザー名に部分一致するか確認
- **重要：ひらがな・カタカナ・ローマ字で表記が違っても、読み方が同じなら同一人物の可能性として扱ってください**
  - 例：「はるか」「ハルカ」「haruka」は同一人物候補
  - 例：「あかり」「アカリ」「akari」は同一人物候補
- 1人だけ該当した → その名前を使用（終了）
- 複数人該当した → ステップ3へ
- 0人該当した → ステップ3へ

**ステップ3: 複数人該当または0人の場合（同姓同名エラー）**
- JSON の name フィールドに 「error_duplicate_name」 と入力
- candidates フィールドに該当した全ユーザー名を配列で入力
- original_input フィールドに入力テキストから抽出した名前を入力
- semester_goal と missions フィールドは空にする
- 理由: 同じ名前の複数ユーザーが存在するため、正確に判定できない

{$userList}

【タスク】
以下のテキストから、各メンバーの目標を抽出して分類してください。

【分類ルール】

**定性的な目標（semester_goal）:**
- 数値なし、または「～を成功させる」「～を達成する」など
- 例: 商談を成功させる、スキルアップする

**定量的な目標（missions）:**
- 数値がある（○本、○回、○個など）
- 例: ブログ5本、資格2個取得、登壇3回

**サイクル（cycle）の判定:**
- 「毎日」「日課」「daily」が含まれる → "weekly"（週間扱い）
- 「毎週」「週に」「週間」が含まれる → "weekly"
- 「毎月」「月に」「月間」が含まれる → "monthly"
- 上記以外 → "none"

【入力テキスト】
{$text}

【出力形式】

成功時:
```json
[
  {
    "name": "正確なユーザー名",
    "semester_goal": "定性的な目標",
    "missions": [
      {"category": "ブログ", "title": "毎週ブログを書く", "count": 1, "cycle": "weekly"},
      {"category": "資格", "title": "月に1冊本を読む", "count": 1, "cycle": "monthly"},
      {"category": "資格", "title": "一陸技取得", "count": 1, "cycle": "none"}
      
    ]
  }
]
```

同姓同名エラー時:
```json
[
  {
    "name": "error_duplicate_name",
    "candidates": ["田中太郎_開発部", "田中太郎_営業部"],
    "original_input": "田中太郎",
    "semester_goal": null,
    "missions": []
  }
]
```

【重要】
- JSONのみを返してください。説明文は不要です
- 必ず上記の形式で返してください
- 名前の判定に迷ったら「error_duplicate_name」を返してください

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