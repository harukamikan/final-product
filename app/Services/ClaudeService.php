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
    public function extractGoals($text)
    {
        $prompt = $this->buildPrompt($text);

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
    protected function buildPrompt($text)
    {
        return <<<PROMPT
あなたはエンジニアの半期目標を抽出するアシスタントです。

以下のテキストから、各メンバーの目標情報を抽出して、JSON形式で返してください。

# 抽出ルール
- 名前（name）: メンバーの名前
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
    "name": "名前",
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
}