<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MissionService;

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

        $missionService->completeWriteTechBlogMission($request->user(), $request->url);

        return back()->with('status', '技術系ブログ投稿ミッションを達成しました！');
    }
}
