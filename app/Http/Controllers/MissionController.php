<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MissionService;
use App\Models\Mission;

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
    public function submitBlogUrl(Request $request, MissionService $missionService)
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        $mission = Mission::where('key', 'write_tech_blog')->firstOrFail();

        $earned = $missionService->handleTrigger(
            $request->user(),
            'tech_blog_posted',
            [
                'mission_key' => $mission->key,
                'url'         => $request->url,
            ]
        );

        return redirect()
            ->route('missions.index')
            ->with([
                'success'      => '技術ブログのURLを登録しました。',
                'earned_miles' => $earned,
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
