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
        $companyId = auth()->user()->company_id;
        
        // TimelineEventをcompany_idで絞り込み、typeでフィルタリング
        $query = TimelineEvent::with('user')
            ->forCompany($companyId)
            ->orderByDesc('occurred_at')
            ->orderByDesc('created_at');
        
        if ($type !== 'all') {
            $query->byType($type);
        }
        
        $events = $query->paginate(20);
        
        return view('timeline.index', compact('events', 'type'));
    }
}
