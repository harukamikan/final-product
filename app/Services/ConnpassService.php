<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ConnpassService
{
    protected Client $client;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.connpass.api_key');
        
        $headers = ['Accept' => 'application/json'];
        if ($this->apiKey) {
            $headers['X-API-Key'] = $this->apiKey;
        }
        
        $this->client = new Client([
            'base_uri' => 'https://connpass.com/api/v2/',
            'headers'  => $headers,
            'timeout'  => 5,
        ]);
    }

    /**
     * connpass URL からイベント ID を抽出
     * 
     * 例: https://connpass.com/event/12345/ → 12345
     */
    public function extractEventIdFromUrl(string $url): ?int
    {
        if (preg_match('#/event/(\d+)#', $url, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * connpass API でイベント情報を取得
     * 
     * @param string $url connpass イベントURL
     * @return array|null イベント情報、取得失敗時は null
     */
    public function fetchEventFromUrl(string $url): ?array
    {
        $eventId = $this->extractEventIdFromUrl($url);
        
        if (!$eventId) {
            Log::warning('connpass: Invalid event URL', ['url' => $url]);
            return null;
        }

        try {
            $response = $this->client->get('events/', [
                'query' => ['event_id' => $eventId]
            ]);
            
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (empty($data['events'])) {
                Log::warning('connpass: No events found', ['event_id' => $eventId]);
                return null;
            }
            
            $event = $data['events'][0];
            
            return [
                'event_id'   => $event['id'],
                'title'      => $event['title'],
                'catch'      => $event['catch'],
                'description' => $event['description'] ?? null,
                'url'        => $event['url'],
                'started_at' => $event['started_at'],
                'ended_at'   => $event['ended_at'],
                'place'      => $event['place'],
                'address'    => $event['address'],
                'accepted'   => $event['accepted'],
                'waiting'    => $event['waiting'],
                'limit'      => $event['limit'],
                'hash_tag'   => $event['hash_tag'] ?? null,
                'owner_nickname' => $event['owner_nickname'] ?? null,
                'api_fetched' => true,
            ];
        } catch (\Throwable $e) {
            Log::warning('connpass API fetch failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
