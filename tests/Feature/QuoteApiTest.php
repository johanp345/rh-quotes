<?php

namespace RH\Quotes\Tests\Feature;

use RH\Quotes\Services\QuoteService;
use Illuminate\Support\Facades\Http;
use RH\Quotes\Tests\TestCase;

class QuoteApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'dummyjson.com/quotes*' => Http::response([
                'quotes' => [['id' => 1, 'quote' => 'Test']],
                'total' => 1
            ]),
            'dummyjson.com/quotes/1' => Http::response(['id' => 1, 'quote' => 'Test']),
            'dummyjson.com/quotes/random' => Http::response(['id' => 99])
        ]);
    }

    /** @test */
    public function it_returns_all_quotes_with_pagination()
    {
        $response = $this->getJson('/api/quotes');

        $response->assertOk()
            ->assertJsonStructure([
                'quotes',
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'total_pages'
                ]
            ]);
    }

    /** @test */
    public function it_returns_single_quote()
    {
        $response = $this->getJson('/api/quotes/1');

        $response->assertOk()
            ->assertJsonStructure(['quotes', 'total'])
            ->assertJsonFragment([
                'id' => 1,
                'quote' => 'Test'
            ]);
    }

    /** @test */
    public function it_returns_random_quote()
    {
        $response = $this->getJson('/api/quotes/random');

        $response->assertOk()
            ->assertJsonStructure(['quotes', 'total'])
            ->assertJsonFragment([
                'id' => 1,
                'quote' => 'Test'
            ]);
    }

}
