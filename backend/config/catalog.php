<?php

return [
    'genshin_db' => [
        'api_base' => env('GENSHIN_DB_API_BASE', 'https://genshin-db-api.vercel.app/api/v5'),
        'source_version' => env('GENSHIN_DB_SOURCE_VERSION', '5.2.15'),
        'game_version' => env('GENSHIN_DB_GAME_VERSION', '7.2'),
        'timeout_seconds' => 30,
        'ca_bundle' => env('GENSHIN_DB_CA_BUNDLE'),
    ],
];
