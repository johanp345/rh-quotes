<?php

namespace RH\Quotes\Tests\Unit;

use RH\Quotes\Services\QuoteService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use RH\Quotes\Tests\TestCase;

class QuoteServiceTest extends TestCase
{
    protected $service;
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = [
            'base_url' => 'https://dummyjson.com',
            'rate_limit' => 5,
            'rate_window' => 60,
            'cache_ttl' => 3600
        ];
        
        $this->service = new QuoteService($this->config);
        Cache::flush();
    }

    /** @test */
    public function it_fetches_and_caches_all_quotes()
    {
        Http::fake([
            'https://dummyjson.com/quotes?limit=1&skip=0' => Http::response([
                'quotes' => [['id' => 1, 'quote' => 'Test']],
                'total' => 1,
                'skip' => 0,
                'limit' => 100
            ])
        ]);

        $result = $this->service->getAllQuotes();
        
        $this->assertCount(30, $result['quotes']);
        $this->assertEquals(30, Cache::get('quotes_cache')->count());
    }

    /** @test */
    public function it_returns_paginated_results()
    {
        $quotes = collect(range(1, 25))->map(fn($i) => ['id' => $i]);
        Cache::put('quotes_cache', $quotes, 3600);

        $result = $this->service->getAllQuotes(1, 10);
        
        $this->assertCount(10, $result['quotes']);
    }

    /** @test */
    public function it_finds_quote_using_binary_search()
    {
        $quotes = collect([
            ['id' => 1], ['id' => 3], ['id' => 5]
        ]);
        Cache::put('quotes_cache', $quotes, 3600);

        $result = $this->service->getQuote(3);
        
        $this->assertEquals(3, $result['id']);
    }

    /** @test */
    public function it_fetches_single_quote_from_api_when_not_in_cache()
    {
        Http::fake([
            'https://dummyjson.com/quotes/2' => Http::response(['id' => 2])
        ]);

        $result = $this->service->getQuote(2);
        
        $this->assertEquals(2, $result['id']);
        $this->assertTrue(Cache::get('quotes_cache')->contains('id', 2));
    }
}