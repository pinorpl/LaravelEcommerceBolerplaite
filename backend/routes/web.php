<?php

use Illuminate\Support\Facades\Route;

// Laravel health check endpoint (used by Docker healthchecks)
Route::get('/', function () {
    return response()->json(['status' => 'ok', 'service' => 'Ecommerce Boilerplate API']);
});
