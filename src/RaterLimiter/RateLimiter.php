<?php

namespace RH\Quotes\RateLimiter;

use Illuminate\Support\Facades\Cache;

class RateLimiter
{
    protected $maxRequests;
    protected $timeWindow;

    public function __construct()
    {
        $this->maxRequests = config('quotes.max_requests', 60);
        $this->timeWindow = config('quotes.time_window', 60);
    }

    public function check()
    {
        $key = 'quotes_rate_limit';
        $requests = Cache::get($key, 0);

        if ($requests >= $this->maxRequests) {
            $resetTime = Cache::get($key . '_reset');
            if ($resetTime && now()->timestamp < $resetTime) {
                sleep($resetTime - now()->timestamp);
            } else {
                Cache::put($key, 0, $this->timeWindow);
                Cache::put($key . '_reset', now()->addSeconds($this->timeWindow)->timestamp, $this->timeWindow);
            }
        }

        Cache::increment($key);
    }
}