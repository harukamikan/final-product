<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function summarize(string $title, string $body): ?string
    {
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            Log::warning('Gemini API key is not set.');
            return null;
        }

        $endpoint = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent';

        // ===== ここから追加：見出し＋本文一部に整形 =====

        $body = trim($body ?? '');
        if ($body === '') {
            return null;
        }

        // 見出し抽出（# / ## / ### まで、最大12個）
        preg_match_all('/^#{1,3}\s+.+$/m', $body, $matches);
        $headings = array_slice($matches[0] ?? [], 0, 12);

        $headingsText = count($headings) > 0
            ? implode("\n", $headings)
            : '（見出しなし）';

        // 本文を短縮：先頭 4000文字 + 末尾 1200文字
        $head = mb_substr($body, 0, 4000);

        $tail = '';
        if (mb_strlen($body) > 5200) { // 4000 + 1200 より長い場合だけ末尾を付ける
            $tail = mb_substr($body, -1200);
        }

        $condensedBody = $head . ($tail ? "\n\n...\n\n" . $tail : '');

        // ===== ここまで追加 =====

        // プロンプト（要約の条件）
        $prompt = <<<PROMPT
あなたはソフトウェアエンジニア向け社内ポータルのアシスタントです。
以下のQiita記事について、エンジニアが一目で内容を把握できるように、
日本語で3〜4文、200文字以内で要約してください。

・読者はエンジニア
・技術的なポイントがわかるように
・箇条書きではなく、文章で書く
・文章が途中で途切れないように、必ず文末は「。」で終えてください
・文字数が足りない場合は内容を圧縮してでも文を完結させてください

タイトル:
{$title}

見出し一覧:
{$headingsText}

本文（一部）:
{$condensedBody}
PROMPT;

        $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint.'?key='.$apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    // 要約は短く出させるので、上限は大きめでOK（途中切れ対策）
                    'maxOutputTokens' => 4096,
                    // 任意：安定させたいなら
                    'temperature' => 0.4,
                ],
            ]);

        if (! $response->successful()) {
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        $json = $response->json();

        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

        // 仕上げ：空や変なときはnull
        $text = $text ? trim($text) : null;

        return $text ?: null;
    }

    /**
     * Score content using AI and return structured JSON response
     * 
     * @param string $prompt
     * @return array|null ['score' => float, 'reason' => string, 'signals' => array]
     */
    public function scoreContent(string $prompt): ?array
    {
        $apiKey = $this->getApiKey();

        if (!$apiKey) {
            Log::warning('Gemini API key is not set for scoring.');
            return null;
        }

        $endpoint = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent';

        try {
            $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->timeout(30)
                ->post($endpoint.'?key='.$apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 1024,
                        'temperature' => 0.3, // Lower temperature for consistent scoring
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Gemini API scoring error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $json = $response->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                return null;
            }

            // Extract JSON from response (handle markdown code blocks)
            $text = trim($text);
            $text = preg_replace('/^```json\s*/m', '', $text);
            $text = preg_replace('/\s*```$/m', '', $text);
            $text = trim($text);

            // Parse JSON
            $result = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse Gemini scoring JSON', [
                    'error' => json_last_error_msg(),
                    'response' => $text,
                ]);
                return null;
            }

            // Validate required fields
            if (!isset($result['score']) || !isset($result['reason'])) {
                Log::error('Invalid Gemini scoring response structure', [
                    'response' => $result,
                ]);
                return null;
            }

            // Ensure score is in valid range
            $result['score'] = max(0.0, min(1.0, (float) $result['score']));

            return $result;
        } catch (\Exception $e) {
            Log::error('Gemini scoring exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get API key for scoring (prefers api_key2, falls back to api_key)
     * 
     * @return string|null
     */
    protected function getApiKey(): ?string
    {
        // Prefer api_key2 for scoring if available
        $key2 = config('services.gemini.api_key2');
        if ($key2) {
            return $key2;
        }

        // Fallback to original api_key
        return config('services.gemini.api_key');
    }
}

