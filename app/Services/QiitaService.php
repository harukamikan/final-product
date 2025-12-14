<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class QiitaService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://qiita.com/api/v2/',
            'headers'  => [
                'Authorization' => 'Bearer ' . config('services.qiita.token'),
                'Accept'        => 'application/json',
            ],
            'timeout' => 5,
        ]);
    }

    /**
     * QiitaのURLから item_id を抜き出す
     *
     * 例: https://qiita.com/ryusei/items/f505a02ba9b96445bd01
     * → f505a02ba9b96445bd01
     */
    public function extractItemIdFromUrl(string $url): ?string
    {
        // パス部分だけ取り出す
        $path = parse_url($url, PHP_URL_PATH); // /ryusei/items/xxxx or /items/xxxx

        if (!$path) {
            return null;
        }

        // /items/{id} を探す
        if (preg_match('#/items/([0-9a-fA-F]+)#', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * QiitaのURLから記事情報を取得する
     *
     * 返すのは、タイトル・本文・タグ・LGTM数・投稿日時
     */
    public function fetchItemFromUrl(string $url): ?array
    {
        $itemId = $this->extractItemIdFromUrl($url);

        if (!$itemId) {
            return null;
        }

        $response = $this->client->get("items/{$itemId}");
        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'item_id'     => $itemId,
            'title'       => $data['title'] ?? '',
            'body'        => $data['body'] ?? '',          // Markdown
            'tags'        => collect($data['tags'] ?? [])->pluck('name')->all(),
            'likes_count' => $data['likes_count'] ?? 0,    // LGTM数
            'created_at'  => $data['created_at'] ?? null,  // ISO8601文字列
            'url'         => $data['url'] ?? $url,
        ];
    }
}
