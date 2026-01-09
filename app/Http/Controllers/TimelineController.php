<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimelineEvent;
use App\Models\QiitaArticle;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TimelineController extends Controller
{
    /**
     * 統合タイムライン表示
     */
    public function index(Request $request)
    {
        $type = $request->query('type', 'all');
        $user = $request->user();
        
        // company_id で絞り込み
        $companyId = $user->company_id;
        
        if ($type === 'all') {
            // 全種類を統合
            $items = $this->fetchAllTimeline($companyId);
        } elseif ($type === 'qiita') {
            $items = $this->fetchQiitaTimeline($companyId);
        } else {
            // event_hosting, event_speaking, certification
            $items = $this->fetchTimelineByType($companyId, $type);
        }
        
        return view('timeline.index', [
            'items' => $items,
            'currentType' => $type,
        ]);
    }
    
    /**
     * 全種類のタイムラインを統合して取得
     */
    protected function fetchAllTimeline(int $companyId)
    {
        // QiitaArticle を取得
        $qiitaItems = QiitaArticle::with('user')
            ->where('company_id', $companyId)
            ->get()
            ->map(fn($article) => (object)[
                'type' => 'qiita',
                'user' => $article->user,
                'occurred_at' => $article->posted_at ?? $article->created_at,
                'created_at' => $article->created_at,
                'data' => $article,
            ]);
        
        // TimelineEvent を取得
        $timelineItems = TimelineEvent::with('user')
            ->where('company_id', $companyId)
            ->get()
            ->map(function($event) {
                // Qiita型のTimelineEventは、QiitaArticle互換のオブジェクトに変換
                if ($event->event_type === 'qiita') {
                    $payload = $event->payload;
                    $dataObj = (object)[
                        'id' => $event->id,
                        'user' => $event->user,
                        'title' => $payload['title'] ?? '',
                        'body' => $payload['body'] ?? '',
                        'summary' => $payload['summary'] ?? null,
                        'tags' => $payload['tags'] ?? [],
                        'likes_count' => $payload['likes_count'] ?? 0,
                        'posted_at' => isset($payload['created_at']) ? \Carbon\Carbon::parse($payload['created_at']) : null,
                        'created_at' => $event->created_at,
                        'url' => $payload['url'] ?? '',
                    ];
                } else {
                    $dataObj = $event;
                }
                
                return (object)[
                    'type' => $event->event_type,
                    'user' => $event->user,
                    'occurred_at' => $event->occurred_at ?? $event->created_at,
                    'created_at' => $event->created_at,
                    'data' => $dataObj,
                ];
            });
        
        // 統合してソート（最新が上）
        $allItems = $qiitaItems->concat($timelineItems)
            ->sortByDesc(function($item) {
                return $item->occurred_at ?? $item->created_at;
            })
            ->values();
        
        // ページネーション
        return $this->paginateCollection($allItems, 20);
    }
    
    /**
     * Qiita タイムラインのみ取得
     */
    protected function fetchQiitaTimeline(int $companyId)
    {
        return QiitaArticle::with('user')
            ->where('company_id', $companyId)
            ->orderByDesc('posted_at')
            ->orderByDesc('created_at')
            ->paginate(20);
    }
    
    /**
     * 特定種別のタイムラインを取得
     */
    protected function fetchTimelineByType(int $companyId, string $type)
    {
        return TimelineEvent::with('user')
            ->where('company_id', $companyId)
            ->where('event_type', $type)
            ->orderByDesc('occurred_at')
            ->paginate(20);
    }
    
    /**
     * Collection をページネーション
     */
    protected function paginateCollection(Collection $items, int $perPage = 20)
    {
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        
        return new LengthAwarePaginator(
            $items->slice($offset, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
