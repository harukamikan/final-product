<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MissionService;
use App\Models\Mission;
use App\Services\QiitaService;
use App\Models\QiitaArticle;
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
    QiitaService $qiitaService
) {
    $request->validate([
        'url' => ['required', 'url'],
    ]);

    $user    = $request->user();
    $url     = $request->input('url');
    $mission = Mission::where('key', 'write_tech_blog')->firstOrFail();

    // 1. Qiita API から記事情報を取得
    try {
        $qiita = $qiitaService->fetchItemFromUrl($url);

        if (!$qiita) {
            return back()
                ->withInput()
                ->withErrors([
                    'url' => 'Qiitaの記事URLの形式ではないようです。',
                ]);
        }
    } catch (\Throwable $e) {
        Log::error('Qiita API error', [
            'url'   => $url,
            'error' => $e->getMessage(),
        ]);

        return back()
            ->withInput()
            ->withErrors([
                'url' => 'Qiita APIから記事情報を取得できませんでした。時間をおいて再度お試しください。',
            ]);
    }

    // 2. ミッション進捗 & マイル付与
    $earned = $missionService->handleTrigger(
        $user,
        'tech_blog_posted',
        [
            'mission_key' => $mission->key,
            'url'         => $qiita['url'] ?? $url,  // Qiita側の正式URLを優先
        ]
    );

    // 3. タイムライン用に Qiita 記事情報を保存
    QiitaArticle::updateOrCreate(
        [
            'user_id' => $user->id,
            'item_id' => $qiita['item_id'],   // QiitaService で返している item_id
        ],
        [
            'mission_id'  => $mission->id,
            'title'       => $qiita['title'] ?? '',
            'body'        => $qiita['body'] ?? '',
            'tags'        => $qiita['tags'] ?? [],
            'likes_count' => $qiita['likes_count'] ?? 0,
            'posted_at'   => !empty($qiita['created_at'])
                ? Carbon::parse($qiita['created_at'])
                : null,
            'url'         => $qiita['url'] ?? $url,
        ]
    );

    // 4. 記事情報を表示（タイトル / 本文 / タグ / LGTM / 投稿日時）
    return view('missions.blog-preview', [
        'mission' => $mission,
        'qiita'   => $qiita,
        'earned'  => $earned,
    ]);
}

    /**
     * GoogleフォームURL入力画面（ここが不足していた）
     */
    public function showGoogleForm(Request $request)
    {
        // URL: /missions/google-form?mission_key=event_speaker など
        $missionKey = $request->query('mission_key');

        if (!$missionKey) {
            abort(404, 'mission_key が指定されていません');
        }

        $mission = Mission::where('key', $missionKey)->firstOrFail();

        return view('missions.google_form', compact('mission'));
    }

    /**
     * GoogleフォームURL送信処理
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
}
