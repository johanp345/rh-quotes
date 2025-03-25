<?php

namespace RH\Quotes\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RH\Quotes\Services\QuoteService;

class QuoteController extends Controller
{
    protected $service;

    public function __construct(QuoteService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', config('quotes.per_page', 10));
        return response()->json($this->service->getAllQuotes($page, $perPage));
    }

    public function random()
    {
        return response()->json($this->service->getRandomQuote());
    }

    public function show($id)
    {
        return response()->json($this->service->getQuote($id));
    }
}
