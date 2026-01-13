<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mission Miles Limits
    |--------------------------------------------------------------------------
    |
    | ミッションkeyごとの報酬マイル（reward_miles）の許容範囲を定義します。
    | ミッション作成時や更新時に、この範囲外の値を設定できないように
    | バリデーションが適用されます。
    |
    */

    'write_tech_blog' => [
        'min_miles' => 30,
        'max_miles' => 60,
    ],

    'event_speaker' => [
        'min_miles' => 60,
        'max_miles' => 100,
    ],

    'event_organizer' => [
        'min_miles' => 70,
        'max_miles' => 120,
    ],

    'acquire_certificate' => [
        'min_miles' => 80,
        'max_miles' => 150,
    ],
];
