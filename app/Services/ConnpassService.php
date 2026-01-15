<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class ConnpassService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://connpass.com/api/v1/',
            'headers'  => [
                'Accept' => 'application/json',
            ],
            'timeout' => 10,
        ]);
    }

    /**
     * Connpass のURLから event_id を抽出する
     *
     * 例: https://connpass.com/event/123456/
     * → 123456
     *
     * @param string $url
     * @return int|null
     */
    public function extractEventIdFromUrl(string $url): ?int
    {
        // パス部分だけ取り出す
        $path = parse_url($url, PHP_URL_PATH);

        if (!$path) {
            return null;
        }

        // /event/{id}/ を探す
        if (preg_match('#/event/(\d+)/?#', $path, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Connpass のURLからイベント情報を取得する
     *
     * 共通フォーマットに変換して返す
     *
     * @param string $url
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchEventFromUrl(string $url): ?array
    {
        $eventId = $this->extractEventIdFromUrl($url);

        if (!$eventId) {
            return null;
        }

        // Connpass API仕様: event_id をクエリパラメータで指定
        // 参考: https://connpass.com/about/api/
        $response = $this->client->get('event/', [
            'query' => [
                'event_id' => $eventId,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        // APIレスポンスの構造: { "results_returned": 1, "events": [...] }
        if (empty($data['events']) || !is_array($data['events'])) {
            return null;
        }

        $event = $data['events'][0] ?? null;

        if (!$event) {
            return null;
        }

        // 共通フォーマットに変換
        return [
            'source' => 'connpass',
            'external_id' => (string) ($event['event_id'] ?? $eventId),
            'title' => $event['title'] ?? '',
            'description' => $event['description'] ?? '',
            'started_at' => $event['started_at'] ?? null,
            'ended_at' => $event['ended_at'] ?? null,
            'place' => $event['place'] ?? null,
            'address' => $event['address'] ?? null,
            'url' => $event['event_url'] ?? $url,
            'owner' => $event['owner_display_name'] ?? null,
            
            // Connpass固有の追加情報
            'additional' => [
                'catch' => $event['catch'] ?? null,
                'hash_tag' => $event['hash_tag'] ?? null,
                'limit' => $event['limit'] ?? null,
                'accepted' => $event['accepted'] ?? 0,
                'waiting' => $event['waiting'] ?? 0,
                'owner_id' => $event['owner_id'] ?? null,
                'owner_nickname' => $event['owner_nickname'] ?? null,
                'series' => $event['series'] ?? null,
            ],
        ];
    }

    /**
     * series_id からイベント情報を取得する（オプション機能）
     *
     * @param int $seriesId
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchEventsBySeriesId(int $seriesId): ?array
    {
        $response = $this->client->get('event/', [
            'query' => [
                'series_id' => $seriesId,
                'order' => 2, // 開催日時順
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['events'] ?? null;
    }
}
