<?php

namespace App\Services;

use App\Models\Mission;
use Illuminate\Support\Facades\Log;

class MileScoringService
{
    protected GeminiService $geminiService;
    protected QiitaService $qiitaService;

    public function __construct(GeminiService $geminiService, QiitaService $qiitaService)
    {
        $this->geminiService = $geminiService;
        $this->qiitaService = $qiitaService;
    }

    /**
     * Score a mission based on its type and payload
     *
     * @param Mission $mission
     * @param array $payload
     * @return array ['score' => float, 'reason' => string, 'miles' => int, 'signals' => array]
     */
    public function scoreMission(Mission $mission, array $payload): array
    {
        try {
            // Route to appropriate scoring method based on mission key
            $scoreResult = match ($mission->key) {
                'write_tech_blog' => $this->scoreQiitaArticle($payload['url'] ?? ''),
                'event_speaker', 'event_organizer', 'acquire_certificate' 
                    => $this->scoreFormSubmission($mission->key, $payload),
                default => $this->fallbackScore(),
            };

            // Calculate miles based on score and mission's min/max
            $miles = $this->calculateMiles(
                $scoreResult['score'],
                $mission->mile_min ?? 0,
                $mission->mile_max ?? 100
            );

            return [
                'score' => $scoreResult['score'],
                'reason' => $scoreResult['reason'],
                'encouragement' => $scoreResult['encouragement'] ?? null,
                'miles' => $miles,
                'signals' => $scoreResult['signals'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Mile scoring failed', [
                'mission_id' => $mission->id,
                'mission_key' => $mission->key,
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackScoreWithMiles($mission);
        }
    }

    /**
     * Score a Qiita article
     *
     * @param string $url
     * @return array
     */
    protected function scoreQiitaArticle(string $url): array
    {
        if (empty($url)) {
            Log::warning('Qiita scoring: empty URL, using fallback');
            return $this->fallbackScore();
        }

        try {
            // Fetch article data using QiitaService
            $article = $this->qiitaService->fetchItemFromUrl($url);

            if (!$article) {
                Log::warning('Qiita scoring: article fetch failed, using fallback');
                return $this->fallbackScore();
            }

            // Build prompt for AI evaluation
            $prompt = $this->buildQiitaPrompt($article);

            // Call Gemini API
            $result = $this->geminiService->scoreContent($prompt);

            if (!$result || !isset($result['score'])) {
                Log::warning('Qiita scoring: AI response invalid, using fallback');
                return $this->fallbackScore();
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Qiita scoring failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackScore();
        }
    }

    /**
     * Score a form-based mission submission
     *
     * @param string $missionKey
     * @param array $formData
     * @return array
     */
    protected function scoreFormSubmission(string $missionKey, array $formData): array
    {
        try {
            // Build prompt based on mission type
            $prompt = $this->buildFormPrompt($missionKey, $formData);

            // Call Gemini API
            $result = $this->geminiService->scoreContent($prompt);

            if (!$result) {
                return $this->fallbackScore();
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Form scoring failed', [
                'mission_key' => $missionKey,
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackScore();
        }
    }

    /**
     * Build AI prompt for Qiita article evaluation
     *
     * @param array $article
     * @return string
     */
    protected function buildQiitaPrompt(array $article): string
    {
        $title = $article['title'] ?? '';
        $body = $article['body'] ?? '';
        $charCount = mb_strlen($body);
        
        // Count headings
        preg_match_all('/^#{1,6}\s+.+$/m', $body, $headingMatches);
        $headingCount = count($headingMatches[0] ?? []);
        
        // Count code blocks
        preg_match_all('/```[\s\S]*?```/', $body, $codeMatches);
        $codeBlockCount = count($codeMatches[0] ?? []);

        return <<<PROMPT
あなたは技術ブログの品質を評価するAIです。以下のQiita記事を評価し、0.0〜1.0のスコアを返してください。

## 評価基準（各0-3点、合計を12で割って正規化）
1. 情報量 (info): 内容の濃さ、文字数、深さ
2. 再利用性 (reuse): 他の人が役立てられるか、コード例の充実度
3. 構造 (structure): 見出し構成、読みやすさ
4. 独自性 (originality): オリジナルな知見や工夫

## 記事情報
タイトル: {$title}
文字数: {$charCount}
見出し数: {$headingCount}
コードブロック数: {$codeBlockCount}

本文（抜粋）:
{$this->truncateText($body, 3000)}

## 返答フォーマット（必ずJSON形式で）
{
  "score": 0.75,
  "reason": "具体的なコード例が豊富で、実践的な内容。構成も明確で読みやすい。",
  "encouragement": "お疲れ様です！素晴らしい技術記事ですね！",
  "signals": {
    "info": 2,
    "reuse": 3,
    "structure": 2,
    "originality": 2
  }
}

注意: 必ずJSONのみを返してください。説明文は不要です。reasonは日本語で1文、30文字以内。encouragementは励ましメッセージ（20文字以内、元気が出る言葉で）。
PROMPT;
    }

    /**
     * Build AI prompt for form-based mission evaluation
     *
     * @param string $missionKey
     * @param array $formData
     * @return string
     */
    protected function buildFormPrompt(string $missionKey, array $formData): string
    {
        $missionType = match ($missionKey) {
            'event_speaker' => '登壇',
            'event_organizer' => 'イベント主催',
            'acquire_certificate' => '資格取得',
            default => 'その他',
        };

        $criteria = match ($missionKey) {
            'event_speaker' => <<<CRITERIA
1. 影響力 (impact): 参加者数、対象範囲（社内/社外）
2. 難易度 (difficulty): 登壇時間、内容の専門性
3. 貢献度 (contribution): コミュニティへの貢献、知見共有度
4. 準備度 (preparation): 資料の充実度、事前準備
CRITERIA,
            'event_organizer' => <<<CRITERIA
1. 規模 (scale): 参加者数、運営の複雑度
2. 企画力 (planning): 独自性、工夫
3. 実行力 (execution): 運営の質、フォロー
4. 影響力 (impact): 社内外への影響、継続性
CRITERIA,
            'acquire_certificate' => <<<CRITERIA
1. 難易度 (difficulty): 資格の難易度、合格率
2. 専門性 (specialty): 業務への関連性、専門性の高さ
3. 学習量 (learning): 必要な学習時間、範囲
4. 市場価値 (value): 資格の認知度、キャリアへの影響
CRITERIA,
            default => '総合的な評価',
        };

        // Extract relevant form fields
        $details = $formData['details'] ?? $formData['title'] ?? '';
        $evidenceUrl = $formData['evidence_url'] ?? $formData['url'] ?? '';
        
        return <<<PROMPT
あなたは技術者の成果を評価するAIです。以下の{$missionType}の成果を評価し、0.0〜1.0のスコアを返してください。

## 評価基準（各0-3点、合計を12で割って正規化）
{$criteria}

## 提出内容
{$this->formatFormData($formData)}

## 返答フォーマット（必ずJSON形式で）
{
  "score": 0.70,
  "reason": "技術的な内容で、コミュニティへの貢献度が高い。",
  "encouragement": "素晴らしい成果です！お疲れ様でした！",
  "signals": {
    "metric1": 2,
    "metric2": 2,
    "metric3": 2,
    "metric4": 2
  }
}

注意: 必ずJSONのみを返してください。説明文は不要です。reasonは日本語で1文、30文字以内。encouragementは励ましメッセージ（20文字以内）。
PROMPT;
    }

    /**
     * Format form data for AI prompt
     *
     * @param array $formData
     * @return string
     */
    protected function formatFormData(array $formData): string
    {
        $formatted = [];
        
        foreach ($formData as $key => $value) {
            if (is_string($value) && !empty($value)) {
                $label = match ($key) {
                    'title' => 'タイトル',
                    'details' => '詳細',
                    'occurred_on' => '実施日',
                    'evidence_url' => '証拠URL',
                    'category' => 'カテゴリ',
                    default => $key,
                };
                
                $formatted[] = "{$label}: {$value}";
            }
        }
        
        return implode("\n", $formatted);
    }

    /**
     * Calculate miles from score and min/max range
     *
     * @param float $score
     * @param int $min
     * @param int $max
     * @return int
     */
    protected function calculateMiles(float $score, int $min, int $max): int
    {
        // Clamp score to 0-1 range
        $score = max(0.0, min(1.0, $score));

        // Calculate miles
        $miles = $min + ($max - $min) * $score;

        // Round to nearest 10
        return (int) round($miles, -1);
    }

    /**
     * Fallback score when AI fails
     *
     * @return array
     */
    protected function fallbackScore(): array
    {
        return [
            'score' => 0.5,
            'reason' => 'AI評価が利用できませんでした。',
            'encouragement' => 'よく頑張りました！',
            'signals' => [],
        ];
    }

    /**
     * Fallback score with miles calculated
     *
     * @param Mission $mission
     * @return array
     */
    protected function fallbackScoreWithMiles(Mission $mission): array
    {
        $fallback = $this->fallbackScore();
        $miles = $this->calculateMiles(
            $fallback['score'],
            $mission->mile_min ?? 0,
            $mission->mile_max ?? 100
        );

        return array_merge($fallback, ['miles' => $miles]);
    }

    /**
     * Truncate text to specified length
     *
     * @param string $text
     * @param int $length
     * @return string
     */
    protected function truncateText(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }
}
