<?php

return [
    'base_url' => env('QUOTES_API_BASE_URL', 'https://dummyjson.com'),
    'rate_limit' => env('QUOTES_RATE_LIMIT', 10),
    'rate_window' => env('QUOTES_RATE_WINDOW', 60),
    'cache_ttl' => env('QUOTES_CACHE_TTL', 3600),
    'max_per_page' => env('QUOTES_MAX_PER_PAGE', 100),
];