<?php

use Illuminate\Support\Facades\Route;
use RH\Quotes\Http\Controllers\QuoteController;

Route::prefix('api/quotes')->group(function () {
    Route::get('/', [QuoteController::class, 'index']);
    Route::get('/random', [QuoteController::class, 'random']);
    Route::get('/{id}', [QuoteController::class, 'show']);
});