<?php

namespace App\Http\Controllers;

use App\Models\TimelineEvent;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        // クエリパラメータ ?type={qiita|event_hosting|event_speaking|certification|all}
        $type = $request->query('type', 'all');
        $keyword = $request->query('keyword');
        $userId = $request->query('user_id');
        
        $companyId = auth()->user()->company_id;
        
        // 同じ会社のユーザー一覧を取得（ユーザーフィルター用）
        $companyUsers = \App\Models\User::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        // TimelineEventをcompany_idで絞り込み、typeでフィルタリング
        $query = TimelineEvent::with('user')
            ->forCompany($companyId)
            ->orderByDesc('occurred_at')
            ->orderByDesc('created_at');
        
        if ($type !== 'all') {
            $query->byType($type);
        }
        
        // キーワード検索
        if ($keyword) {
            $query->searchKeyword($keyword);
        }
        
        // ユーザーフィルター
        if ($userId) {
            $query->forUser($userId);
        }
        
        $events = $query->paginate(20);
        
        return view('timeline.index', compact('events', 'type', 'keyword', 'userId', 'companyUsers'));
    }
}
