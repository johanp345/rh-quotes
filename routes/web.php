<?php

use Illuminate\Support\Facades\Route;

Route::get('/quotes-ui', function () {
    return view('quotes::quotes');
});