<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MissionService;
use App\Models\Mission;
use App\Services\QiitaService;
use App\Models\QiitaArticle;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MissionController extends Controller
{
    /**
     * 技術ブログ（Qiita）URL入力画面
     */
    public function showBlogUrlForm()
    {
        $mission = Mission::where('key', 'write_tech_blog')->firstOrFail();
        return view('missions.blog_url', compact('mission'));
    }

    /**
     * 技術ブログ（Qiita）URL送信処理
     */
    public function submitBlogUrl(
        Request $request,
        MissionService $missionService,
        QiitaService $qiitaService,
        GeminiService $geminiService
    ) {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        $user    = $request->user();
        $url     = $request->input('url');
        $mission = Mission::where('key', 'write_tech_blog')->firstOrFail();

        // 1) Qiita API から記事情報を取得
        try {
            $qiita = $qiitaService->fetchItemFromUrl($url);

            if (!$qiita) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'url' => 'Qiita APIから記事情報を取得できませんでした。時間をおいて再度お試しください。',
                    ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Qiita fetch failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'url' => 'Qiita記事の取得に失敗しました。URLを確認して再度お試しください。',
                ]);
        }

        // 2) Geminiで要約を生成（失敗してもミッション処理は続行）
        $summary = null;
        try {
            $summary = $geminiService->summarize($qiita['title'] ?? '', $qiita['body'] ?? '');
        } catch (\Throwable $e) {
            Log::warning('Gemini summarize failed', [
                'url' => $qiita['url'] ?? $url,
                'error' => $e->getMessage(),
            ]);
        }

        // 3) ミッション進捗 & マイル付与
        $achievementData = $missionService->handleTrigger(
            $user,
            'tech_blog_posted',
            [
                'mission_key' => $mission->key,
                'url'         => $qiita['url'] ?? $url,
            ]
        );

        // 4) タイムライン用に Qiita 記事情報を保存
        QiitaArticle::updateOrCreate(
            [
                'user_id' => $user->id,
                'item_id' => $qiita['item_id'],   // QiitaServiceで返している item_id
            ],
            [
                'mission_id'  => $mission->id,
                'title'       => $qiita['title'] ?? '',
                'body'        => $qiita['body'] ?? '',
                'summary'     => $summary,
                'tags'        => $qiita['tags'] ?? [],
                'likes_count' => $qiita['likes_count'] ?? 0,
                'posted_at'   => !empty($qiita['created_at'])
                    ? Carbon::parse($qiita['created_at'])
                    : null,
                'url'         => $qiita['url'] ?? $url,
            ]
        );

        // 5) プレビュー表示
        return view('missions.blog-preview', [
            'mission' => $mission,
            'qiita'   => $qiita,
            'achievementData' => $achievementData,
        ]);
    }

    /**
     * （旧）GoogleフォームURL入力画面
     * ※ 今はアプリ内フォームに移行したので、ルートから外していれば未使用になります
     */
    public function showGoogleForm(Request $request)
    {
        $missionKey = $request->query('mission_key');

        if (!$missionKey) {
            abort(404, 'mission_key が指定されていません');
        }

        $mission = Mission::where('key', $missionKey)->firstOrFail();

        return view('missions.google_form', compact('mission'));
    }

    /**
     * （旧）GoogleフォームURL送信処理
     * ※ 今はアプリ内フォームに移行したので、ルートから外していれば未使用になります
     */
    public function storeGoogleForm(Request $request, MissionService $missionService)
    {
        $request->validate([
            'mission_key' => ['required', 'in:event_speaker,event_organizer,acquire_certificate'],
            'url'         => ['required', 'url'],
        ]);

        $mission = Mission::where('key', $request->mission_key)->firstOrFail();

        $earned = $missionService->handleTrigger(
            $request->user(),
            'google_form_submitted',
            [
                'mission_key' => $mission->key,
                'url'         => $request->url,
            ]
        );

        return redirect()
            ->route('missions.index')
            ->with([
                'success'      => "ミッション「{$mission->title}」の報告を送信しました。",
                'earned_miles' => $earned,
            ]);
    }

    /**
     * ミッション詳細 or 適切な入力画面へリダイレクト
     */
    public function show(Mission $mission)
    {
        return match ($mission->key) {

            // 技術ブログ（Qiita）
            'write_tech_blog' => redirect()->route('missions.blog-url.form'),

            // ✅ アプリ内フォームへ（Googleフォームではなく）
            'event_speaker',
            'event_organizer',
            'acquire_certificate'
            => redirect()->route('missions.form.create', ['mission' => $mission]),

            default => view('missions.show', compact('mission')),
        };
    }
}
