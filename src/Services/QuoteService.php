<?php

namespace RH\Quotes\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class QuoteService
{
    protected $config;
    protected $httpClient;
    protected $cachedQuotes;
    protected $cacheKey = 'quotes_cache';
    protected $cacheTtl = 3600; // 1 hora en segundos

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->httpClient = Http::baseUrl($config['base_url']);
        $this->initializeCache();
    }

    private function initializeCache()
    {
        $this->cachedQuotes = Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            return collect();
        });
    }

    public function getAllQuotes(int $page = 1, int $perPage = 10)
    {
        $this->handleRateLimit();

        if ($this->cachedQuotes->isEmpty()) {
            $this->fetchAndCacheAllQuotes();
        }

        return $this->paginate($page, $perPage);
    }

    private function fetchAndCacheAllQuotes()
    {
        $currentPage = 1;
        $totalQuotes = [];
        $totalPages = 1;

        do {
            $response = $this->httpClient->get('/quotes', [
                'limit' => 100, // Máximo permitido por la API
                'skip' => ($currentPage - 1) * 100
            ]);

            $data = $response->json();
            $totalQuotes = array_merge($totalQuotes, $data['quotes']);
            $totalPages = ceil($data['total'] / 100);
            $currentPage++;
            
            $this->handleRateLimit();

        } while ($currentPage <= $totalPages);

        // Almacenar ordenado para búsqueda binaria
        usort($totalQuotes, fn($a, $b) => $a['id'] <=> $b['id']);
        $this->cachedQuotes = collect($totalQuotes);
        Cache::put($this->cacheKey, $this->cachedQuotes, $this->cacheTtl);
    }

    private function paginate(int $page, int $perPage)
    {
        $total = $this->cachedQuotes->count();
        $paginated = $this->cachedQuotes
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return [
            'quotes' => $paginated,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    private function binarySearch(int $id)
    {
        $low = 0;
        $high = $this->cachedQuotes->count() - 1;

        while ($low <= $high) {
            $mid = (int)(($low + $high) / 2);
            $currentId = $this->cachedQuotes[$mid]['id'];

            if ($currentId === $id) {
                return $mid;
            }

            $currentId < $id ? $low = $mid + 1 : $high = $mid - 1;
        }

        return -1;
    }

    public function getQuote(int $id)
    {
        $this->handleRateLimit();
        $response = $this->httpClient->get("/quotes/{$id}");
        
        if ($response->successful()) {
            $quote = $response->json();
            $this->addToCache($quote);
            return $quote;
        }

        return null;
    }

    public function getRandomQuote()
    {
        $this->handleRateLimit();
        $response = $this->httpClient->get('/quotes/random');
        if ($response->successful()) {
            $quote = $response->json();
            $this->addToCache($quote);
            return $quote;
        }
        return null;
    }

    private function addToCache(array $quote)
    {
        $this->cachedQuotes->push($quote);
        $this->cachedQuotes = $this->cachedQuotes->sortBy('id')->values();
        Cache::put($this->cacheKey, $this->cachedQuotes, $this->cacheTtl);
    }

    private function handleRateLimit()
    {
        $key = 'quotes_rate_limit';
        $window = now()->timestamp / $this->config['rate_window'];
        $data = Cache::get($key, ['count' => 0, 'window' => floor($window)]);

        if ($data['window'] != floor($window)) {
            $data = ['count' => 0, 'window' => floor($window)];
        }

        if (++$data['count'] > $this->config['rate_limit']) {
            $remaining = ceil(($data['window'] + 1) * $this->config['rate_window'] - now()->timestamp);
            sleep($remaining);
            $data = ['count' => 1, 'window' => floor(now()->timestamp / $this->config['rate_window'])];
        }

        Cache::put($key, $data, $this->config['rate_window']);
    }

    private function insertIntoSortedCache(array $quote)
    {
        $this->cachedQuotes[] = $quote;
        usort($this->cachedQuotes, fn($a, $b) => $a['id'] <=> $b['id']);
    }
}