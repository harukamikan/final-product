<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MissionService;
use App\Models\Mission;

class MissionController extends Controller
{
    public function showBlogUrlForm()
    {
        return view('missions.blog_url');
    }

    public function submitBlogUrl(Request $request, MissionService $missionService)
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        // ブログ投稿トリガーを発火
        $missionService->handleTrigger(
            $request->user(),
            'tech_blog_posted',
            ['url' => $request->url]
        );

        return back()->with('status', '技術系ブログ投稿ミッションを達成しました！');
    }

}
